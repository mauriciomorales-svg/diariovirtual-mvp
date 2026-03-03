<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Jobs\TransformNewsJob;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador de desarrollo para Gemini - Sin autenticación requerida
 */
class GeminiDevController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
        // NO aplicar middleware de auth para desarrollo local
    }

    /**
     * Muestra el formulario de importación rápida
     */
    public function showImportForm()
    {
        return view('admin.gemini.import');
    }

    /**
     * Muestra el formulario de importación enhanced (versión desarrollo)
     */
    public function showEnhancedImportForm()
    {
        return view('dev.gemini.import-enhanced');
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

            Log::info('Dev: Gemini transformation initiated', [
                'title' => $title,
                'source' => $sourceName,
                'mode' => $processingMode
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
            Log::error('Dev: Gemini transformation failed', [
                'error' => $e->getMessage(),
                'title' => $request->input('title')
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
                        'gemini_processed' => true,
                        'published_by_dev' => true
                    ]),
                ]
            );

            Log::info('Dev: Published Gemini-transformed article', [
                'article_id' => $article->id,
                'title' => $article->title
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Noticia publicada exitosamente',
                'article_id' => $article->id
            ]);
        } catch (\Exception $e) {
            Log::error('Dev: Failed to publish article', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al publicar la noticia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Health check del servicio Gemini - AHORA detecta cuota agotada
     */
    public function healthCheck()
    {
        try {
            $health = $this->geminiService->healthCheck();

            return response()->json([
                'success' => true,
                'healthy' => $health['available'],
                'quota_exceeded' => $health['quota_exceeded'] ?? false,
                'model' => $health['model'] ?? 'unknown',
                'error' => $health['error'],
                'message' => $health['available'] 
                    ? 'Gemini service is healthy' 
                    : ($health['quota_exceeded'] ? 'Cuota agotada - espera 24 horas' : 'Gemini service is down'),
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Dev: Gemini health check failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'healthy' => false,
                'quota_exceeded' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas de uso de Gemini
     */
    public function getStats()
    {
        try {
            $health = $this->geminiService->healthCheck();
            
            $stats = [
                'total_articles' => \App\Models\Article::whereNotNull('metadata')->count(),
                'gemini_processed' => \App\Models\Article::where('metadata', 'like', '%gemini_processed%')->count(),
                'recent_articles' => \App\Models\Article::where('published_at', '>=', now()->subDays(7))->count(),
                'queue_pending' => \Illuminate\Support\Facades\Queue::size('gemini-transform'),
                'service_healthy' => $health['available'] ?? false,
                'quota_exceeded' => $health['quota_exceeded'] ?? false,
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Dev: Failed to get Gemini stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Diagnóstico completo del servicio Gemini
     */
    public function getDiagnostics()
    {
        try {
            $diagnostics = $this->geminiService->getDiagnostics();

            return response()->json([
                'success' => true,
                'diagnostics' => $diagnostics,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Dev: Failed to get Gemini diagnostics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
