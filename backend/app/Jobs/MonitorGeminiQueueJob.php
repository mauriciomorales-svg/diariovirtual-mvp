<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;

class MonitorGeminiQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 60;

    protected $alertThresholds = [
        'queue_size' => 50,      // Alerta si hay más de 50 jobs
        'processing_time' => 300, // Alerta si un job toma más de 5 minutos
        'failure_rate' => 10,     // Alerta si más del 10% falla
    ];

    public function handle(): void
    {
        try {
            $queueSize = Queue::size('gemini-transform');
            $failedJobs = $this->getFailedJobsCount();
            $processingJobs = $this->getProcessingJobsCount();

            // Calcular métricas
            $totalJobs = $queueSize + $processingJobs;
            $failureRate = $totalJobs > 0 ? ($failedJobs / $totalJobs) * 100 : 0;

            $metrics = [
                'queue_size' => $queueSize,
                'processing_jobs' => $processingJobs,
                'failed_jobs' => $failedJobs,
                'failure_rate' => $failureRate,
                'total_jobs' => $totalJobs,
                'timestamp' => now()->toISOString(),
            ];

            // Guardar métricas en cache
            Cache::put('gemini_queue_metrics', $metrics, 300); // 5 minutos

            // Verificar alertas
            $this->checkAlerts($metrics);

            // Log de estado
            Log::info('Gemini queue monitoring', $metrics);

        } catch (\Exception $e) {
            Log::error('Gemini queue monitoring failed', [
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ]);
        }
    }

    /**
     * Verifica si se deben disparar alertas
     */
    protected function checkAlerts(array $metrics): void
    {
        $alerts = [];

        // Alerta por tamaño de cola
        if ($metrics['queue_size'] > $this->alertThresholds['queue_size']) {
            $alerts[] = "Queue size too high: {$metrics['queue_size']} jobs";
        }

        // Alerta por tasa de fallos
        if ($metrics['failure_rate'] > $this->alertThresholds['failure_rate']) {
            $alerts[] = "High failure rate: {$metrics['failure_rate']}%";
        }

        // Alerta si hay jobs procesándose por mucho tiempo
        if ($metrics['processing_jobs'] > 10) {
            $alerts[] = "Too many jobs processing: {$metrics['processing_jobs']}";
        }

        // Disparar alertas si hay alguna
        if (!empty($alerts)) {
            $this->triggerAlerts($alerts, $metrics);
        }
    }

    /**
     * Dispara las alertas (logging, notifications, etc.)
     */
    protected function triggerAlerts(array $alerts, array $metrics): void
    {
        foreach ($alerts as $alert) {
            Log::warning('Gemini queue alert', [
                'alert' => $alert,
                'metrics' => $metrics,
                'timestamp' => now()->toISOString()
            ]);
        }

        // Aquí se podrían agregar otros métodos de notificación:
        // - Email
        // - Slack
        // - Webhook
        // - SMS
    }

    /**
     * Obtiene el conteo de jobs fallidos
     */
    protected function getFailedJobsCount(): int
    {
        try {
            // Contar jobs fallidos de las últimas 24 horas
            $failedJobs = \Illuminate\Support\Facades\DB::table('failed_jobs')
                ->where('queue', 'gemini-transform')
                ->where('failed_at', '>=', now()->subHours(24))
                ->count();

            return $failedJobs;
        } catch (\Exception $e) {
            Log::error('Failed to get failed jobs count', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Obtiene el conteo de jobs procesándose actualmente
     */
    protected function getProcessingJobsCount(): int
    {
        try {
            // Intentar obtener desde Redis si está disponible
            if (app()->bound('redis')) {
                $redis = app('redis');
                $processingJobs = $redis->command('LLEN', ['queues:gemini-transform:reserved']);
                return (int) $processingJobs;
            }

            // Fallback a database
            return \Illuminate\Support\Facades\DB::table('jobs')
                ->where('queue', 'gemini-transform')
                ->count();
        } catch (\Exception $e) {
            Log::error('Failed to get processing jobs count', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Programa el próximo monitoreo
     */
    public function schedule(): void
    {
        // Programar para ejecutarse cada 2 minutos
        self::dispatch()
            ->onQueue('monitoring')
            ->delay(now()->addMinutes(2));
    }

    /**
     * Determina en qué cola debe ir el job
     */
    public function queue(): string
    {
        return 'monitoring';
    }

    /**
     * Etiqueta para monitoreo
     */
    public function tags(): array
    {
        return [
            'monitoring',
            'gemini',
            'queue_health'
        ];
    }
}
