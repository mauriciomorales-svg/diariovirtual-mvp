<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\AutoPublishJob;
use App\Jobs\TransformNewsJob;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GeminiEnhancedController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
        // Middleware is applied via route group, not in constructor
    }

    /**
     * Muestra el formulario de importación mejorado
     */
    public function showEnhancedImportForm()
    {
        return view('admin.gemini.import-enhanced');
    }

    /**
     * Procesa la importación con configuración avanzada
     */
    public function processEnhancedImport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:50',
            'source_url' => 'required|url',
            'source_name' => 'required|string|max:100',
            'processing_mode' => 'required|in:sync,async',
            'temperature' => 'nullable|numeric|min:0|max:1',
            'maxLength' => 'nullable|in:short,medium,long',
            'localStyle' => 'nullable|in:malleco,angol,victoria,collipulli',
            'autoPublish' => 'nullable|boolean',
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
            $autoPublish = $request->input('autoPublish', false);

            // Configuración avanzada
            $advancedConfig = [
                'temperature' => $request->input('temperature', 0.7),
                'maxLength' => $request->input('maxLength', 'medium'),
                'localStyle' => $request->input('localStyle', 'malleco'),
                'autoPublish' => $autoPublish,
            ];

            Log::info('Admin initiated enhanced Gemini transformation', [
                'title' => $title,
                'source' => $sourceName,
                'mode' => $processingMode,
                'config' => $advancedConfig,
                'user_id' => auth()->id()
            ]);

            if ($processingMode === 'sync') {
                // Procesamiento síncrono con configuración avanzada
                $transformed = $this->geminiService->transformArticleAdvanced(
                    $content, 
                    $title, 
                    $advancedConfig
                );
                
                // Auto-publicar si está configurado
                if ($autoPublish) {
                    $this->autoPublishArticle($transformed, $sourceUrl, $sourceName);
                }
                
                return response()->json([
                    'success' => true,
                    'mode' => 'preview',
                    'data' => $transformed,
                    'auto_published' => $autoPublish,
                    'message' => $autoPublish ? 'Transformación completada y publicada automáticamente' : 'Transformación completada. Revisa y publica.'
                ]);

            } else {
                // Procesamiento asíncrono con configuración avanzada
                $job = TransformNewsJob::dispatch($content, $title, $sourceUrl, $sourceName)
                    ->onQueue('gemini-transform');
                
                // Auto-publicar si está configurado
                if ($autoPublish) {
                    $job->chain([
                        new AutoPublishJob($sourceUrl, $sourceName)
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'mode' => 'queued',
                    'auto_published' => $autoPublish,
                    'message' => 'Noticia enviada a procesamiento' . ($autoPublish ? ' y se publicará automáticamente' : '. Será procesada pronto.')
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Admin enhanced Gemini transformation failed', [
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
     * Obtiene estadísticas enriquecidas
     */
    public function getEnhancedStats()
    {
        try {
            // Estadísticas básicas
            $basicStats = [
                'total_articles' => DB::table('articles')->count(),
                'gemini_processed' => DB::table('articles')->where('metadata', 'like', '%gemini_processed%')->count(),
                'recent_articles' => DB::table('articles')->where('published_at', '>=', now()->subDays(7))->count(),
                'queue_pending' => \Illuminate\Support\Facades\Queue::size('gemini-transform'),
            ];

            // Estadísticas avanzadas
            $advancedStats = [
                'processing_time_avg' => $this->getAverageProcessingTime(),
                'success_rate' => $this->getSuccessRate(),
                'popular_sources' => $this->getPopularSources(),
                'local_focus_distribution' => $this->getLocalFocusDistribution(),
                'word_count_stats' => $this->getWordCountStats(),
                'daily_volume' => $this->getDailyVolume(),
            ];

            // Métricas de rendimiento
            $performanceMetrics = [
                'cache_hit_rate' => $this->getCacheHitRate(),
                'api_response_time' => $this->getApiResponseTime(),
                'error_rate' => $this->getErrorRate(),
                'queue_throughput' => $this->getQueueThroughput(),
            ];

            return response()->json([
                'success' => true,
                'stats' => array_merge($basicStats, $advancedStats),
                'performance' => $performanceMetrics,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get enhanced Gemini stats', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene sugerencias de contenido basadas en IA
     */
    public function getContentSuggestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:50',
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

            // Analizar contenido y generar sugerencias
            $suggestions = $this->generateContentSuggestions($title, $content);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate content suggestions', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenera contenido con diferentes parámetros
     */
    public function regenerateContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'original_title' => 'required|string|max:255',
            'original_content' => 'required|string|min:50',
            'previous_result' => 'required|array',
            'regeneration_type' => 'required|in:more_local,different_angle,shorter,longer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $originalTitle = $request->input('original_title');
            $originalContent = $request->input('original_content');
            $previousResult = $request->input('previous_result');
            $regenerationType = $request->input('regeneration_type');

            // Configurar parámetros basados en el tipo de regeneración
            $config = $this->getRegenerationConfig($regenerationType, $previousResult);

            $newResult = $this->geminiService->transformArticleAdvanced(
                $originalContent,
                $originalTitle,
                $config
            );

            Log::info('Content regenerated', [
                'type' => $regenerationType,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'data' => $newResult,
                'regeneration_type' => $regenerationType,
                'message' => 'Contenido regenerado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to regenerate content', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guarda borrador automáticamente
     */
    public function saveDraft(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:50',
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
            $draft = [
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'source_url' => $request->input('source_url'),
                'source_name' => $request->input('source_name'),
                'user_id' => auth()->id(),
                'created_at' => now()->toISOString(),
            ];

            // Guardar en cache por 24 horas
            $draftKey = 'gemini_draft_' . auth()->id() . '_' . uniqid();
            Cache::put($draftKey, $draft, 86400); // 24 horas

            Log::info('Draft saved', [
                'draft_key' => $draftKey,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'draft_key' => $draftKey,
                'message' => 'Borrador guardado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save draft', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Métodos privados para estadísticas avanzadas

    private function getAverageProcessingTime(): float
    {
        try {
            $avgTime = DB::table('articles')
                ->where('metadata', 'like', '%processing_time%')
                ->selectRaw('AVG(CAST(JSON_EXTRACT(metadata, "$.processing_time") AS DECIMAL(10,2))) as avg_time')
                ->value('avg_time');

            return (float) ($avgTime ?? 0);
        } catch (\Exception $e) {
            Log::error('Error getting average processing time', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getSuccessRate(): float
    {
        try {
            $total = DB::table('job_batches')->count();
            $successful = DB::table('job_batches')->where('failed_jobs', 0)->count();
            
            return $total > 0 ? ($successful / $total) * 100 : 100;
        } catch (\Exception $e) {
            Log::error('Error getting success rate', ['error' => $e->getMessage()]);
            return 100;
        }
    }

    private function getPopularSources(): array
    {
        try {
            return DB::table('articles')
                ->whereNotNull('metadata')
                ->selectRaw('JSON_EXTRACT(metadata, "$.original_source") as source, COUNT(*) as count')
                ->groupBy('source')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'source' => $item->source,
                        'count' => $item->count
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting popular sources', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function getLocalFocusDistribution(): array
    {
        try {
            return DB::table('articles')
                ->whereNotNull('metadata')
                ->selectRaw('JSON_EXTRACT(metadata, "$.local_focus") as focus, COUNT(*) as count')
                ->groupBy('focus')
                ->orderByDesc('count')
                ->get()
                ->map(function ($item) {
                    return [
                        'focus' => $item->focus,
                        'count' => $item->count
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting local focus distribution', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function getWordCountStats(): array
    {
        try {
            $stats = DB::table('articles')
                ->whereNotNull('metadata')
                ->selectRaw('
                    AVG(CAST(JSON_EXTRACT(metadata, "$.word_count") AS UNSIGNED)) as avg,
                    MIN(CAST(JSON_EXTRACT(metadata, "$.word_count") AS UNSIGNED)) as min,
                    MAX(CAST(JSON_EXTRACT(metadata, "$.word_count") AS UNSIGNED)) as max
                ')
                ->first();

            return [
                'average' => (int) ($stats->avg ?? 0),
                'minimum' => (int) ($stats->min ?? 0),
                'maximum' => (int) ($stats->max ?? 0),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting word count stats', ['error' => $e->getMessage()]);
            return ['average' => 0, 'minimum' => 0, 'maximum' => 0];
        }
    }

    private function getDailyVolume(): array
    {
        try {
            return DB::table('articles')
                ->where('published_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(published_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'count' => $item->count
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting daily volume', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function getCacheHitRate(): float
    {
        try {
            // Simular métricas de cache hit rate
            $totalRequests = Cache::get('gemini_cache_total_requests', 0);
            $cacheHits = Cache::get('gemini_cache_hits', 0);
            
            return $totalRequests > 0 ? ($cacheHits / $totalRequests) * 100 : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getApiResponseTime(): float
    {
        try {
            $avgTime = Cache::get('gemini_avg_response_time', 0);
            return (float) $avgTime;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getErrorRate(): float
    {
        try {
            $total = DB::table('failed_jobs')->where('queue', 'gemini-transform')->count();
            $successful = DB::table('job_batches')->sum('processed_jobs');
            $totalProcessed = $total + $successful;
            
            return $totalProcessed > 0 ? ($total / $totalProcessed) * 100 : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getQueueThroughput(): float
    {
        try {
            $jobsPerHour = DB::table('job_batches')
                ->where('created_at', '>=', now()->subHour())
                ->sum('total_jobs');
            
            return (float) $jobsPerHour;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function generateContentSuggestions(string $title, string $content): array
    {
        // Analizar contenido y generar sugerencias
        $suggestions = [];

        // Sugerir palabras clave
        $keywords = $this->extractKeywords($content);
        if (!empty($keywords)) {
            $suggestions['keywords'] = $keywords;
        }

        // Sugerir enfoque local
        $localFocus = $this->suggestLocalFocus($content);
        if ($localFocus) {
            $suggestions['local_focus'] = $localFocus;
        }

        // Sugerir longitud óptima
        $wordCount = str_word_count($content);
        $suggestions['length_optimization'] = [
            'current' => $wordCount,
            'recommended' => $this->getRecommendedLength($wordCount),
            'reason' => $this->getLengthRecommendationReason($wordCount)
        ];

        return $suggestions;
    }

    private function extractKeywords(string $content): array
    {
        // Extraer palabras clave simples (puede mejorarse con NLP)
        $words = str_word_count(strtolower($content), 1);
        $stopWords = ['el', 'la', 'de', 'que', 'en', 'y', 'a', 'los', 'del', 'se', 'las', 'por', 'un', 'con', 'para', 'como', 'las', 'uno', 'si', 'ya', 'sus', 'al', 'lo', 'le', 'más'];
        
        $keywords = array_filter($words, function($word) use ($stopWords) {
            return !in_array($word, $stopWords) && strlen($word) > 3;
        });

        return array_count_values($keywords);
        arsort($keywords);
        
        return array_slice(array_keys($keywords), 0, 10);
    }

    private function suggestLocalFocus(string $content): string
    {
        $localTerms = ['malleco', 'angol', 'victoria', 'collipulli', 'araucanía', 'temuco', ' Renaico', 'purén'];
        
        foreach ($localTerms as $term) {
            if (stripos($content, $term) !== false) {
                return ucfirst($term);
            }
        }
        
        return 'Malleco'; // Default
    }

    private function getRecommendedLength(int $current): int
    {
        if ($current < 150) return 200;
        if ($current > 300) return 250;
        return $current;
    }

    private function getLengthRecommendationReason(int $wordCount): string
    {
        if ($wordCount < 150) return 'El contenido es muy corto para un artículo completo';
        if ($wordCount > 300) return 'El contenido es muy largo, considera acortarlo para mejor legibilidad';
        return 'La longitud es apropiada';
    }

    private function getRegenerationConfig(string $type, array $previousResult): array
    {
        $baseConfig = [
            'temperature' => 0.7,
            'maxLength' => 'medium',
            'localStyle' => 'malleco',
        ];

        switch ($type) {
            case 'more_local':
                $baseConfig['temperature'] = 0.8;
                $baseConfig['localStyle'] = 'angol';
                $baseConfig['prompt_modification'] = 'Aumenta el enfoque local con menciones específicas de comunidades de Malleco';
                break;
                
            case 'different_angle':
                $baseConfig['temperature'] = 0.9;
                $baseConfig['prompt_modification'] = 'Usa un ángulo completamente diferente para abordar la misma noticia';
                break;
                
            case 'shorter':
                $baseConfig['maxLength'] = 'short';
                $baseConfig['prompt_modification'] = 'Haz el contenido más conciso y directo';
                break;
                
            case 'longer':
                $baseConfig['maxLength'] = 'long';
                $baseConfig['prompt_modification'] = 'Expande el contenido con más detalles y contexto';
                break;
        }

        return $baseConfig;
    }

    private function autoPublishArticle(array $transformed, string $sourceUrl, string $sourceName): void
    {
        try {
            \App\Models\Article::updateOrCreate(
                [
                    'source_hash' => hash('sha256', $sourceUrl)
                ],
                [
                    'title' => $transformed['title'],
                    'slug' => $transformed['slug'],
                    'excerpt' => $transformed['excerpt'],
                    'content' => $transformed['content'],
                    'image_url' => $transformed['image_url'],
                    'is_external' => true,
                    'external_url' => $sourceUrl,
                    'status' => 'published',
                    'published_at' => now(),
                    'metadata' => json_encode(array_merge(
                        $transformed['metadata'] ?? [],
                        [
                            'original_source' => $sourceName,
                            'auto_published' => true,
                            'published_by' => auth()->id(),
                            'gemini_processed' => true
                        ]
                    )),
                ]
            );

            Log::info('Article auto-published', [
                'title' => $transformed['title'],
                'user_id' => auth()->id()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to auto-publish article', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        }
    }
}
