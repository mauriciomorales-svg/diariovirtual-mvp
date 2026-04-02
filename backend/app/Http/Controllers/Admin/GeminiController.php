<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\TransformNewsJob;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Validator;

class GeminiController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Muestra el formulario de importación rápida
     */
    public function showImportForm()
    {
        return view('admin.gemini.import');
    }

    /**
     * Procesa la importación de noticias
     */
    public function processImport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:50',
            'source_url' => 'required|url',
            'source_name' => 'required|string|max:100',
            'processing_mode' => 'required|in:sync,async',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $title = $request->input('title');
            $content = $request->input('content');
            $sourceUrl = $request->input('source_url');
            $sourceName = $request->input('source_name');
            $processingMode = $request->input('processing_mode');

            Log::info('Admin initiated Gemini transformation', [
                'title' => $title,
                'source' => $sourceName,
                'mode' => $processingMode,
                'user_id' => auth()->id()
            ]);

            if ($processingMode === 'sync') {
                // Procesamiento síncrono para preview
                $transformed = $this->geminiService->transformArticle($content, $title);
                
                return response()->json([
                    'success' => true,
                    'mode' => 'preview',
                    'data' => $transformed,
                    'message' => 'Transformación completada. Revisa y publica.'
                ]);

            } else {
                // Procesamiento asíncrono
                TransformNewsJob::dispatch($content, $title, $sourceUrl, $sourceName)
                    ->onQueue('gemini-transform');

                return response()->json([
                    'success' => true,
                    'mode' => 'queued',
                    'message' => 'Noticia enviada a procesamiento. Será publicada automáticamente.'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Admin Gemini transformation failed', [
                'error' => $e->getMessage(),
                'title' => $request->input('title'),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al procesar la noticia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Publica una noticia transformada
     */
    public function publishTransformed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'excerpt' => 'required|string|max:255',
            'content' => 'required|string',
            'image_url' => 'required|url',
            'source_url' => 'required|url',
            'source_name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $article = \App\Models\Article::updateOrCreate(
                [
                    'source_hash' => hash('sha256', $request->input('source_url'))
                ],
                [
                    'title' => $request->input('title'),
                    'slug' => $request->input('slug'),
                    'excerpt' => $request->input('excerpt'),
                    'content' => $request->input('content'),
                    'image_url' => $request->input('image_url'),
                    'is_external' => true,
                    'external_url' => $request->input('source_url'),
                    'status' => 'published',
                    'published_at' => now(),
                    'metadata' => json_encode([
                        'original_source' => $request->input('source_name'),
                        'local_focus' => 'admin_published',
                        'urgency_level' => 'high',
                        'word_count' => str_word_count($request->input('content')),
                        'published_by' => auth()->id(),
                        'gemini_processed' => true
                    ]),
                ]
            );

            Log::info('Admin published Gemini-transformed article', [
                'article_id' => $article->id,
                'title' => $article->title,
                'user_id' => auth()->id(),
                'was_recently_created' => $article->wasRecentlyCreated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Noticia publicada exitosamente',
                'article_id' => $article->id,
                'article_url' => route('articles.show', $article->slug)
            ]);

        } catch (\Exception $e) {
            Log::error('Admin failed to publish article', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al publicar la noticia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Health check del servicio Gemini
     */
    public function healthCheck()
    {
        try {
            $health = $this->geminiService->healthCheck();
            $ok = (bool) ($health['available'] ?? false);

            return response()->json([
                'success' => true,
                'healthy' => $ok,
                'message' => $ok
                    ? 'Gemini service is healthy'
                    : ($health['error'] ?? 'Gemini service is down'),
                'details' => $health,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gemini health check failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->check() ? auth()->id() : null,
            ]);

            return response()->json([
                'success' => false,
                'healthy' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas de uso de Gemini
     */
    public function getStats()
    {
        try {
            // No llamar a healthCheck() aquí: en paralelo con /health duplica trabajo y si algo falla
            // (DB, caché, red) el panel entero rompe. Las cuentas son solo lectura a BD.
            $geminiProcessed = $this->safeCountGeminiMetadata();

            $totalArticles = $this->safeCount(fn () => \App\Models\Article::whereNotNull('metadata')->count());
            $recentArticles = $this->safeCount(fn () => \App\Models\Article::where('published_at', '>=', now()->subDays(7))->count());

            $queuePending = 0;
            try {
                $queuePending = Queue::size('gemini-transform');
            } catch (\Throwable $e) {
                Log::debug('Queue size not available', ['error' => $e->getMessage()]);
            }

            $stats = [
                'total_articles' => $totalArticles,
                'gemini_processed' => $geminiProcessed,
                'recent_articles' => $recentArticles,
                'queue_pending' => $queuePending,
                'service_healthy' => ! empty(config('services.gemini.api_key')),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to get Gemini stats', [
                'error' => $e->getMessage(),
                'user_id' => auth()->check() ? auth()->id() : null,
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param  callable(): int  $fn
     */
    private function safeCount(callable $fn): int
    {
        try {
            return (int) $fn();
        } catch (\Throwable $e) {
            Log::warning('Gemini stats count failed', ['error' => $e->getMessage()]);

            return 0;
        }
    }

    private function safeCountGeminiMetadata(): int
    {
        try {
            return $this->countArticlesWithGeminiMetadata();
        } catch (\Throwable $e) {
            Log::warning('Gemini metadata count failed', ['error' => $e->getMessage()]);

            return 0;
        }
    }

    /**
     * LIKE sobre JSON falla en PostgreSQL; usamos cast seguro.
     */
    private function countArticlesWithGeminiMetadata(): int
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            try {
                return (int) DB::table('articles')
                    ->whereNotNull('metadata')
                    ->whereRaw("metadata::text LIKE ?", ['%gemini_processed%'])
                    ->count();
            } catch (\Throwable $e) {
                Log::warning('pgsql gemini metadata count fallback', ['error' => $e->getMessage()]);

                return (int) DB::table('articles')->whereNotNull('metadata')->count();
            }
        }

        try {
            return \App\Models\Article::where('metadata', 'like', '%gemini_processed%')->count();
        } catch (\Throwable $e) {
            return \App\Models\Article::whereNotNull('metadata')->count();
        }
    }
}
