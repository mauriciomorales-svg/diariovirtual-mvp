<?php

namespace App\Jobs;

use App\Services\GeminiService;
use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransformNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120]; // 30s, 1min, 2min
    public $timeout = 120; // 2 minutos máximo

    protected $originalContent;
    protected $originalTitle;
    protected $originalUrl;
    protected $originalSource;

    public function __construct(string $originalContent, string $originalTitle, string $originalUrl, string $originalSource)
    {
        $this->originalContent = $originalContent;
        $this->originalTitle = $originalTitle;
        $this->originalUrl = $originalUrl;
        $this->originalSource = $originalSource;
    }

    public function handle(GeminiService $gemini): void
    {
        try {
            Log::info('Starting Gemini transformation', [
                'original_title' => $this->originalTitle,
                'original_source' => $this->originalSource,
                'attempt' => $this->attempts()
            ]);

            // Transformar con Gemini
            $transformed = $gemini->transformArticle($this->originalContent, $this->originalTitle);

            // Crear o actualizar artículo
            $article = Article::updateOrCreate(
                [
                    'source_hash' => hash('sha256', $this->originalUrl)
                ],
                [
                    'title' => $transformed['title'],
                    'slug' => $transformed['slug'],
                    'excerpt' => $transformed['excerpt'],
                    'content' => $transformed['content'],
                    'image_url' => $transformed['image_url'],
                    'is_external' => true,
                    'external_url' => $this->originalUrl,
                    'status' => 'published',
                    'published_at' => now(),
                    'metadata' => json_encode($transformed['metadata'] ?? []),
                ]
            );

            Log::info('Gemini transformation completed successfully', [
                'article_id' => $article->id,
                'title' => $transformed['title'],
                'was_recently_created' => $article->wasRecentlyCreated,
                'processing_time' => $transformed['metadata']['processing_time'] ?? 'N/A'
            ]);

        } catch (\Exception $e) {
            Log::error('Gemini transformation failed', [
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'original_title' => $this->originalTitle,
                'original_source' => $this->originalSource,
                'max_tries_reached' => $this->attempts() >= $this->tries
            ]);

            // Si es el último intento, crear un artículo de fallback
            if ($this->attempts() >= $this->tries) {
                $this->createFallbackArticle();
            }

            throw $e; // Para que el sistema de colas maneje el retry
        }
    }

    /**
     * Crea un artículo de fallback si Gemini falla completamente
     */
    private function createFallbackArticle(): void
    {
        try {
            $fallbackTitle = '🚨 ' . $this->originalTitle;
            $fallbackSlug = Str::slug($this->originalTitle);
            $fallbackExcerpt = substr(strip_tags($this->originalContent), 0, 252) . '...';
            
            // Insertar placeholder después del segundo párrafo
            $content = $this->originalContent;
            $paragraphs = explode("\n\n", $content);
            if (count($paragraphs) > 2) {
                array_splice($paragraphs, 2, 0, '[NATIVE_AD_PLACEHOLDER]');
                $content = implode("\n\n", $paragraphs);
            } else {
                $content .= "\n\n[NATIVE_AD_PLACEHOLDER]";
            }

            Article::updateOrCreate(
                [
                    'source_hash' => hash('sha256', $this->originalUrl)
                ],
                [
                    'title' => $fallbackTitle,
                    'slug' => $fallbackSlug,
                    'excerpt' => $fallbackExcerpt,
                    'content' => $content,
                    'image_url' => 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco',
                    'is_external' => true,
                    'external_url' => $this->originalUrl,
                    'status' => 'published',
                    'published_at' => now(),
                    'metadata' => json_encode([
                        'original_source' => $this->originalSource,
                        'local_focus' => 'fallback_processing',
                        'urgency_level' => 'medium',
                        'word_count' => str_word_count($content),
                        'gemini_failed' => true,
                        'fallback_used' => true
                    ]),
                ]
            );

            Log::warning('Created fallback article due to Gemini failure', [
                'original_title' => $this->originalTitle,
                'fallback_title' => $fallbackTitle
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create fallback article', [
                'error' => $e->getMessage(),
                'original_title' => $this->originalTitle
            ]);
        }
    }

    /**
     * Determina en qué cola debe ir el job
     */
    public function queue(): string
    {
        return 'gemini-transform';
    }

    /**
     * Etiqueta para monitoreo
     */
    public function tags(): array
    {
        return [
            'gemini',
            'transform',
            'source:' . $this->originalSource,
            'attempt:' . $this->attempts()
        ];
    }
}
