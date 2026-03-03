<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GeminiQueueWorker extends Command
{
    protected $signature = 'gemini:queue-worker 
                            {--queue=gemini-transform : Queue to process}
                            {--memory=128 : Memory limit in MB}
                            {--sleep=3 : Seconds to sleep when no jobs}
                            {--timeout=300 : Maximum execution time}
                            {--tries=3 : Number of attempts before failing}';

    protected $description = 'Specialized queue worker for Gemini AI processing';

    public function handle(): int
    {
        $queue = $this->option('queue');
        $memory = $this->option('memory');
        $sleep = $this->option('sleep');
        $timeout = $this->option('timeout');
        $tries = $this->option('tries');

        $this->info("Starting Gemini queue worker...");
        $this->info("Queue: {$queue}");
        $this->info("Memory limit: {$memory}MB");
        $this->info("Sleep: {$sleep}s");
        $this->info("Timeout: {$timeout}s");
        $this->info("Tries: {$tries}");

        // Configurar límite de memoria
        ini_set('memory_limit', "{$memory}M");

        // Configurar handler para señales
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'handleSignal']);
            pcntl_signal(SIGINT, [$this, 'handleSignal']);
        }

        $startTime = time();
        $jobsProcessed = 0;
        $jobsFailed = 0;

        try {
            while (true) {
                // Verificar timeout
                if (time() - $startTime > $timeout) {
                    $this->info("Timeout reached, stopping worker...");
                    break;
                }

                // Verificar señales
                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }

                // Procesar jobs
                $job = $this->getNextJob($queue);

                if ($job) {
                    $this->processJob($job, $tries);
                    $jobsProcessed++;
                } else {
                    // Dormir si no hay jobs
                    $this->sleep($sleep);
                }

                // Verificar uso de memoria
                $memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
                if ($memoryUsage > $memory * 0.9) {
                    $this->warn("Memory usage high ({$memoryUsage}MB), restarting worker...");
                    break;
                }

                // Log de progreso cada 10 jobs
                if ($jobsProcessed % 10 === 0 && $jobsProcessed > 0) {
                    $this->info("Processed {$jobsProcessed} jobs...");
                }
            }

        } catch (\Exception $e) {
            Log::error('Gemini queue worker crashed', [
                'error' => $e->getMessage(),
                'jobs_processed' => $jobsProcessed,
                'jobs_failed' => $jobsFailed
            ]);

            $this->error("Worker crashed: {$e->getMessage()}");
            return 1;
        }

        $this->info("Worker stopped. Processed {$jobsProcessed} jobs, failed {$jobsFailed} jobs.");
        
        Log::info('Gemini queue worker stopped', [
            'jobs_processed' => $jobsProcessed,
            'jobs_failed' => $jobsFailed,
            'runtime' => time() - $startTime
        ]);

        return 0;
    }

    /**
     * Obtiene el siguiente job de la cola
     */
    protected function getNextJob(string $queue): ?object
    {
        try {
            $job = \Illuminate\Support\Facades\Queue::pop($queue);
            
            if ($job) {
                $this->line("Got job: " . get_class($job));
            }

            return $job;
        } catch (\Exception $e) {
            Log::error('Failed to get next job', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Procesa un job individual
     */
    protected function processJob(object $job, int $maxTries): void
    {
        $attempt = 1;
        
        while ($attempt <= $maxTries) {
            try {
                $this->line("Processing job (attempt {$attempt})...");
                
                $startTime = microtime(true);
                $job->handle();
                $processingTime = (microtime(true) - $startTime) * 1000; // ms

                $this->info("✅ Job completed in {$processingTime}ms");
                
                Log::info('Gemini job completed', [
                    'job_class' => get_class($job),
                    'attempt' => $attempt,
                    'processing_time_ms' => $processingTime
                ]);

                return;

            } catch (\Exception $e) {
                $this->error("❌ Job failed (attempt {$attempt}): {$e->getMessage()}");
                
                Log::warning('Gemini job failed', [
                    'job_class' => get_class($job),
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);

                if ($attempt < $maxTries) {
                    $attempt++;
                    $this->sleep(5 * $attempt); // Backoff exponencial
                } else {
                    // Marcar como fallido
                    $this->markJobAsFailed($job, $e);
                    return;
                }
            }
        }
    }

    /**
     * Marca un job como fallido
     */
    protected function markJobAsFailed(object $job, \Exception $e): void
    {
        try {
            if (method_exists($job, 'failed')) {
                $job->failed($e);
            }

            $this->error("Job marked as failed after {$this->option('tries')} attempts");
            
            Log::error('Gemini job failed permanently', [
                'job_class' => get_class($job),
                'error' => $e->getMessage(),
                'attempts' => $this->option('tries')
            ]);

        } catch (\Exception $failedException) {
            Log::error('Failed to mark job as failed', [
                'original_error' => $e->getMessage(),
                'failed_error' => $failedException->getMessage()
            ]);
        }
    }

    /**
     * Duerme el worker por un tiempo específico
     */
    protected function sleep(int $seconds): void
    {
        $this->line("Sleeping for {$seconds} seconds...");
        sleep($seconds);
    }

    /**
     * Maneja señales del sistema
     */
    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        $this->info("Received signal {$signal}, stopping gracefully...");
        
        Log::info('Gemini queue worker received signal', [
            'signal' => $signal,
            'timestamp' => now()->toISOString()
        ]);
        
        return false; // Continue with normal termination
    }
}
