<?php

namespace App\Jobs;

use App\Jobs\TransformNewsJob;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class BatchTransformNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 300; // 5 minutos para batch completo

    protected $articles;
    protected $batchId;

    public function __construct(array $articles)
    {
        $this->articles = $articles;
        $this->batchId = 'batch_' . uniqid();
    }

    public function handle(): void
    {
        try {
            Log::info('Starting batch Gemini transformation', [
                'batch_id' => $this->batchId,
                'articles_count' => count($this->articles)
            ]);

            // Crear batch de jobs
            $jobs = collect($this->articles)->map(function ($article) {
                return new TransformNewsJob(
                    $article['content'],
                    $article['title'],
                    $article['url'],
                    $article['source']
                );
            });

            // Ejecutar batch con callbacks
            $batch = Bus::batch($jobs->toArray())
                ->then(function (Batch $batch) {
                    Log::info('Batch Gemini transformation completed', [
                        'batch_id' => $batch->id,
                        'total_jobs' => $batch->totalJobs,
                        'processed_jobs' => $batch->processedJobs()
                    ]);
                })
                ->catch(function (Batch $batch, \Throwable $e) {
                    Log::error('Batch Gemini transformation failed', [
                        'batch_id' => $batch->id,
                        'failed_jobs' => $batch->failedJobs,
                        'error' => $e->getMessage()
                    ]);
                })
                ->finally(function (Batch $batch) {
                    Log::info('Batch Gemini transformation finished', [
                        'batch_id' => $batch->id,
                        'success_rate' => $batch->totalJobs > 0 ? ($batch->processedJobs() / $batch->totalJobs) * 100 : 0
                    ]);
                })
                ->onQueue('gemini-transform')
                ->dispatch();

            Log::info('Batch Gemini transformation queued', [
                'batch_id' => $batch->id,
                'total_jobs' => $batch->totalJobs
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create batch Gemini transformation', [
                'batch_id' => $this->batchId,
                'error' => $e->getMessage(),
                'articles_count' => count($this->articles)
            ]);

            throw $e;
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
            'batch',
            'batch_id:' . $this->batchId,
            'articles_count:' . count($this->articles)
        ];
    }
}
