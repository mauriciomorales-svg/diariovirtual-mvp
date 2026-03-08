<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\ImageExtractorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoPublishJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];
    public $timeout = 60;

    protected $sourceUrl;
    protected $sourceName;
    protected $transformedData;

    /**
     * Create a new job instance.
     */
    public function __construct(string $sourceUrl, string $sourceName, ?array $transformedData = null)
    {
        $this->sourceUrl = $sourceUrl;
        $this->sourceName = $sourceName;
        $this->transformedData = $transformedData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('AutoPublishJob started', [
                'source_url' => $this->sourceUrl,
                'source_name' => $this->sourceName,
                'has_transformed_data' => !is_null($this->transformedData)
            ]);

            // Si tenemos datos transformados, usarlos directamente
            if ($this->transformedData) {
                $this->publishArticle($this->transformedData);
            } else {
                // Buscar el artículo por source_hash
                $article = Article::where('source_hash', hash('sha256', $this->sourceUrl))->first();
                
                if ($article) {
                    // Asegurar que esté publicado
                    if ($article->status !== 'published') {
                        $article->update([
                            'status' => 'published',
                            'published_at' => now(),
                        ]);
                        
                        Log::info('Article auto-published', [
                            'article_id' => $article->id,
                            'title' => $article->title
                        ]);
                    } else {
                        Log::info('Article already published', [
                            'article_id' => $article->id
                        ]);
                    }
                } else {
                    Log::warning('Article not found for auto-publishing', [
                        'source_url' => $this->sourceUrl
                    ]);
                }
            }

            Log::info('AutoPublishJob completed successfully');

        } catch (\Exception $e) {
            Log::error('AutoPublishJob failed', [
                'error' => $e->getMessage(),
                'source_url' => $this->sourceUrl,
                'attempt' => $this->attempts()
            ]);

            throw $e;
        }
    }

    /**
     * Publish article with transformed data
     */
    private function publishArticle(array $transformedData): void
    {
        $imageUrl = $transformedData['image_url'] ?? 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco';
        if (str_contains($imageUrl, 'via.placeholder.com')) {
            $extracted = app(ImageExtractorService::class)->extractFromUrl($this->sourceUrl);
            if ($extracted) {
                $imageUrl = $extracted;
            }
        }

        $article = Article::updateOrCreate(
            [
                'source_hash' => hash('sha256', $this->sourceUrl)
            ],
            [
                'title' => $transformedData['title'],
                'slug' => $transformedData['slug'],
                'excerpt' => $transformedData['excerpt'],
                'content' => $transformedData['content'],
                'image_url' => $imageUrl,
                'is_external' => true,
                'external_url' => $this->sourceUrl,
                'status' => 'published',
                'published_at' => now(),
                'metadata' => json_encode(array_merge(
                    $transformedData['metadata'] ?? [],
                    [
                        'original_source' => $this->sourceName,
                        'auto_published' => true,
                        'published_at' => now()->toISOString(),
                        'gemini_processed' => true
                    ]
                )),
            ]
        );

        Log::info('Article created/updated via auto-publish', [
            'article_id' => $article->id,
            'title' => $article->title,
            'was_recently_created' => $article->wasRecentlyCreated
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AutoPublishJob failed permanently', [
            'error' => $exception->getMessage(),
            'source_url' => $this->sourceUrl,
            'source_name' => $this->sourceName
        ]);
    }

    /**
     * Determine the queue this job should be on.
     */
    public function queue(): string
    {
        return 'gemini-transform';
    }

    /**
     * Get the tags for the job.
     */
    public function tags(): array
    {
        return [
            'auto-publish',
            'source:' . $this->sourceName,
            'url:' . substr(hash('sha256', $this->sourceUrl), 0, 8)
        ];
    }
}
