<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\GeminiController;
use App\Http\Controllers\Admin\GeminiBatchController;
use App\Http\Controllers\Admin\GeminiEnhancedController;
use App\Http\Controllers\Admin\GeminiBatchImportController;
use App\Http\Controllers\Dev\GeminiDevController;
use App\Models\Article;

Route::get('/', function () {
    return view('welcome');
});

// Test API endpoint
Route::get('/test-api', function () {
    try {
        $articles = Article::published()
            ->orderBy('published_at', 'desc')
            ->take(5)
            ->get(['title', 'slug', 'excerpt']);
        
        return response()->json([
            'status' => 'success',
            'message' => 'API working correctly',
            'total_articles' => Article::count(),
            'published_articles' => Article::published()->count(),
            'sample_articles' => $articles
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Admin routes for Gemini functionality - REQUIERE AUTENTICACIÓN
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Individual article processing
    Route::get('/gemini/import', [GeminiController::class, 'showImportForm'])->name('admin.gemini.import');
    Route::post('/gemini/process', [GeminiController::class, 'processImport'])->name('admin.gemini.process');
    Route::post('/gemini/publish', [GeminiController::class, 'publishTransformed'])->name('admin.gemini.publish');
    Route::get('/gemini/health', [GeminiController::class, 'healthCheck'])->name('admin.gemini.health');
    Route::get('/gemini/stats', [GeminiController::class, 'getStats'])->name('admin.gemini.stats');

    // Batch processing
    Route::get('/gemini/batch', [GeminiBatchController::class, 'showBatchForm'])->name('admin.gemini.batch');
    Route::post('/gemini/batch/process', [GeminiBatchController::class, 'processBatch'])->name('admin.gemini.batch.process');
    Route::get('/gemini/batch/status', [GeminiBatchController::class, 'getBatchStatus'])->name('admin.gemini.batch.status');
    Route::post('/gemini/batch/monitor', [GeminiBatchController::class, 'startQueueMonitor'])->name('admin.gemini.batch.monitor');
    Route::post('/gemini/batch/cleanup', [GeminiBatchController::class, 'cleanupFailedJobs'])->name('admin.gemini.batch.cleanup');
    Route::post('/gemini/batch/retry', [GeminiBatchController::class, 'retryFailedJobs'])->name('admin.gemini.batch.retry');

    // Enhanced processing
    Route::get('/gemini/enhanced', [GeminiEnhancedController::class, 'showEnhancedImportForm'])->name('admin.gemini.enhanced');
    Route::post('/gemini/enhanced/process', [GeminiEnhancedController::class, 'processEnhancedImport'])->name('admin.gemini.enhanced.process');
    Route::get('/gemini/enhanced/stats', [GeminiEnhancedController::class, 'getEnhancedStats'])->name('admin.gemini.enhanced.stats');
    Route::post('/gemini/enhanced/suggestions', [GeminiEnhancedController::class, 'getContentSuggestions'])->name('admin.gemini.enhanced.suggestions');
    Route::post('/gemini/enhanced/regenerate', [GeminiEnhancedController::class, 'regenerateContent'])->name('admin.gemini.enhanced.regenerate');
    Route::post('/gemini/enhanced/draft', [GeminiEnhancedController::class, 'saveDraft'])->name('admin.gemini.enhanced.draft');

    // Batch processing
    Route::get('/gemini/batch-import', [GeminiBatchImportController::class, 'showBatchImportForm'])->name('admin.gemini.batch-import');
    Route::post('/gemini/batch-process', [GeminiBatchImportController::class, 'processBatchImport'])->name('admin.gemini.batch-process');
});

// Development routes - NO AUTH required for local testing
Route::middleware(['web'])->prefix('dev')->group(function () {
    // Gemini routes accessible without auth for local development
    Route::get('/gemini/import', [GeminiDevController::class, 'showImportForm'])->name('dev.gemini.import');
    Route::post('/gemini/process', [GeminiDevController::class, 'processImport'])->name('dev.gemini.process');
    Route::post('/gemini/publish', [GeminiDevController::class, 'publishTransformed'])->name('dev.gemini.publish');
    Route::get('/gemini/health', [GeminiDevController::class, 'healthCheck'])->name('dev.gemini.health');
    Route::get('/gemini/stats', [GeminiDevController::class, 'getStats'])->name('dev.gemini.stats');
    Route::get('/gemini/diagnostics', [GeminiDevController::class, 'getDiagnostics'])->name('dev.gemini.diagnostics');

    // Enhanced processing - USANDO GeminiDevController
    Route::get('/gemini/enhanced', [GeminiDevController::class, 'showEnhancedImportForm'])->name('dev.gemini.enhanced');

    // Batch processing - Development (SIN CSRF PARA TESTING)
    Route::get('/gemini/batch-import', [GeminiBatchImportController::class, 'showBatchImportForm'])->name('dev.gemini.batch-import');
    Route::post('/gemini/batch-process', [GeminiBatchImportController::class, 'processBatchImport'])->name('dev.gemini.batch-process')->middleware('no-csrf');
});

// Test routes for debugging
Route::get('/test-image-proxy', function () {
    $testUrl = 'https://picsum.photos/1200/630';
    $encoded = base64_encode($testUrl);
    return "<a href='/image-proxy/{$encoded}'>Test Image Proxy</a><br>Encoded: {$encoded}";
});

Route::get('/debug-image-proxy', function () {
    try {
        $testUrl = 'https://picsum.photos/1200/630';
        $response = \Illuminate\Support\Facades\Http::timeout(10)->get($testUrl);

        if ($response->successful()) {
            return "HTTP Request successful. Size: " . strlen($response->body()) . " bytes";
        } else {
            return "HTTP Request failed. Status: " . $response->status();
        }
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
