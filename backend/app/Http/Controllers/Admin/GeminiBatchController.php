<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BatchTransformNewsJob;
use App\Jobs\MonitorGeminiQueueJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class GeminiBatchController extends Controller
{
    /**
     * Muestra el formulario de procesamiento batch
     */
    public function showBatchForm()
    {
        return view('admin.gemini.batch');
    }

    /**
     * Procesa múltiples artículos en batch
     */
    public function processBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'articles' => 'required|array|min:1|max:50',
            'articles.*.title' => 'required|string|max:255',
            'articles.*.content' => 'required|string|min:50',
            'articles.*.url' => 'required|url',
            'articles.*.source' => 'required|string|max:100',
            'batch_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $articles = $request->input('articles');
            $batchName = $request->input('batch_name', 'batch_' . uniqid());

            Log::info('Admin initiated batch Gemini transformation', [
                'batch_name' => $batchName,
                'articles_count' => count($articles),
                'user_id' => auth()->id()
            ]);

            // Dispatch batch job
            BatchTransformNewsJob::dispatch($articles);

            return response()->json([
                'success' => true,
                'message' => 'Batch procesado exitosamente',
                'batch_name' => $batchName,
                'articles_count' => count($articles),
                'estimated_time' => count($articles) * 30 . ' segundos'
            ]);

        } catch (\Exception $e) {
            Log::error('Admin batch Gemini transformation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al procesar batch: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene el estado del batch
     */
    public function getBatchStatus(Request $request)
    {
        try {
            $batchId = $request->input('batch_id');
            
            // Obtener métricas del cache
            $metrics = Cache::get('gemini_queue_metrics', [
                'queue_size' => 0,
                'processing_jobs' => 0,
                'failed_jobs' => 0,
                'failure_rate' => 0,
                'total_jobs' => 0,
                'timestamp' => now()->toISOString()
            ]);

            // Obtener estadísticas de batches
            $batchStats = $this->getBatchStatistics();

            return response()->json([
                'success' => true,
                'metrics' => $metrics,
                'batch_stats' => $batchStats,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get batch status', [
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
     * Inicia el monitor de colas
     */
    public function startQueueMonitor()
    {
        try {
            MonitorGeminiQueueJob::dispatch();

            Log::info('Queue monitor started by admin', [
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Monitor de colas iniciado'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to start queue monitor', [
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
     * Obtiene estadísticas detalladas de batches
     */
    protected function getBatchStatistics(): array
    {
        try {
            // Obtener estadísticas de job_batches
            $batchStats = \Illuminate\Support\Facades\DB::table('job_batches')
                ->where('created_at', '>=', now()->subDays(7))
                ->selectRaw('
                    COUNT(*) as total_batches,
                    SUM(total_jobs) as total_jobs,
                    SUM(pending_jobs) as pending_jobs,
                    SUM(failed_jobs) as failed_jobs,
                    AVG(CASE WHEN total_jobs > 0 THEN (processed_jobs / total_jobs) * 100 ELSE 0 END) as avg_success_rate
                ')
                ->first();

            // Obtener estadísticas de jobs individuales
            $jobStats = \Illuminate\Support\Facades\DB::table('jobs')
                ->where('queue', 'gemini-transform')
                ->selectRaw('
                    COUNT(*) as current_queue_size,
                    AVG(CASE WHEN attempts > 0 THEN attempts ELSE 1 END) as avg_attempts
                ')
                ->first();

            // Obtener jobs fallidos
            $failedStats = \Illuminate\Support\Facades\DB::table('failed_jobs')
                ->where('queue', 'gemini-transform')
                ->where('failed_at', '>=', now()->subHours(24))
                ->selectRaw('
                    COUNT(*) as failed_24h,
                    MAX(failed_at) as last_failure
                ')
                ->first();

            return [
                'batches' => [
                    'total' => $batchStats->total_batches ?? 0,
                    'total_jobs' => $batchStats->total_jobs ?? 0,
                    'pending_jobs' => $batchStats->pending_jobs ?? 0,
                    'failed_jobs' => $batchStats->failed_jobs ?? 0,
                    'success_rate' => round($batchStats->avg_success_rate ?? 0, 2)
                ],
                'queue' => [
                    'current_size' => $jobStats->current_queue_size ?? 0,
                    'avg_attempts' => round($jobStats->avg_attempts ?? 1, 2)
                ],
                'failures' => [
                    'last_24h' => $failedStats->failed_24h ?? 0,
                    'last_failure' => $failedStats->last_failure
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get batch statistics', ['error' => $e->getMessage()]);
            
            return [
                'batches' => ['total' => 0, 'total_jobs' => 0, 'pending_jobs' => 0, 'failed_jobs' => 0, 'success_rate' => 0],
                'queue' => ['current_size' => 0, 'avg_attempts' => 1],
                'failures' => ['last_24h' => 0, 'last_failure' => null]
            ];
        }
    }

    /**
     * Limpia jobs fallidos antiguos
     */
    public function cleanupFailedJobs()
    {
        try {
            $days = $request->input('days', 7);
            
            $deleted = \Illuminate\Support\Facades\DB::table('failed_jobs')
                ->where('queue', 'gemini-transform')
                ->where('failed_at', '<', now()->subDays($days))
                ->delete();

            Log::info('Admin cleaned up failed jobs', [
                'deleted_count' => $deleted,
                'days' => $days,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$deleted} jobs fallidos",
                'deleted_count' => $deleted
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cleanup failed jobs', [
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
     * Reinicia jobs fallidos
     */
    public function retryFailedJobs()
    {
        try {
            $limit = $request->input('limit', 10);
            
            $failedJobs = \Illuminate\Support\Facades\DB::table('failed_jobs')
                ->where('queue', 'gemini-transform')
                ->orderBy('failed_at', 'desc')
                ->limit($limit)
                ->get();

            $retried = 0;
            
            foreach ($failedJobs as $failedJob) {
                try {
                    // Re-queue the failed job
                    $payload = json_decode($failedJob->payload, true);
                    $jobClass = $payload['displayName'];
                    
                    if (class_exists($jobClass)) {
                        $job = unserialize($payload['data']['command']);
                        dispatch($job)->onQueue('gemini-transform');
                        
                        // Delete from failed jobs
                        \Illuminate\Support\Facades\DB::table('failed_jobs')
                            ->where('id', $failedJob->id)
                            ->delete();
                        
                        $retried++;
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to retry job', [
                        'job_id' => $failedJob->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Admin retried failed jobs', [
                'retried_count' => $retried,
                'attempted_count' => count($failedJobs),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Se reintentaron {$retried} jobs fallidos",
                'retried_count' => $retried,
                'attempted_count' => count($failedJobs)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retry failed jobs', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
