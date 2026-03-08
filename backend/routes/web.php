<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\GeminiController;
use App\Http\Controllers\Admin\GeminiBatchController;
use App\Http\Controllers\Admin\GeminiEnhancedController;
use App\Http\Controllers\Admin\GeminiBatchImportController;
use App\Http\Controllers\Admin\ArticleAdminController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Dev\GeminiDevController;
use App\Http\Controllers\Dev\ArticleDevController;
use App\Http\Controllers\Dev\ExternalNewsController;
use App\Http\Controllers\Dev\DashboardController as DevDashboardController;
use App\Models\Article;

Route::get('/', function () {
    return view('welcome');
});

// API de artículos para el frontend
Route::get('/api/batch-articles', function () {
    $placeholder = 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco';
    $articles = \App\Models\Article::where('status', 'published')
        ->orderBy('created_at', 'desc')
        ->limit(30)
        ->get(['id', 'title', 'slug', 'source_hash', 'excerpt', 'content', 'image_url', 'published_at', 'is_external', 'external_url']);

    $data = $articles->map(function ($article) use ($placeholder) {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'source_hash' => $article->source_hash ?? '',
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'image_url' => !empty($article->image_url) ? $article->image_url : $placeholder,
            'published_at' => $article->published_at?->format('Y-m-d H:i:s') ?? $article->created_at?->format('Y-m-d H:i:s'),
            'is_external' => (bool) $article->is_external,
            'external_url' => $article->external_url,
            'status' => 'published',
        ];
    })->values()->all();

    return response()->json([
        'data' => $data,
        'current_page' => 1,
        'per_page' => 30,
        'total' => count($data),
        'last_page' => 1,
        'showing' => 'Mostrando ' . count($data) . ' noticias recientes',
    ]);
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

// Admin routes - REQUIERE AUTENTICACIÓN
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

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

    // Gestión de artículos (cambiar imagen)
    Route::get('/articles', [ArticleAdminController::class, 'index'])->name('admin.articles.index');
    Route::get('/articles/{article}/edit-image', [ArticleAdminController::class, 'editImage'])->name('admin.articles.edit-image');
    Route::post('/articles/{article}/update-image', [ArticleAdminController::class, 'updateImage'])->name('admin.articles.update-image');
    Route::get('/articles/{article}/extract-source', [ArticleAdminController::class, 'extractFromSource'])->name('admin.articles.extract-source');
});

// Development routes - NO AUTH required for local testing
Route::middleware(['web'])->prefix('dev')->group(function () {
    Route::get('/', [DevDashboardController::class, 'index'])->name('dev.dashboard');
    Route::get('/dashboard', [DevDashboardController::class, 'index']);

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

    // Traer noticias externas (preview + confirmación, máx 30)
    Route::get('/news/external', [ExternalNewsController::class, 'showForm'])->name('dev.news.external');
    Route::post('/news/external/fetch', [ExternalNewsController::class, 'fetch'])->name('dev.news.external.fetch');
    Route::post('/news/external/import', [ExternalNewsController::class, 'import'])->name('dev.news.external.import');

    // Gestión de artículos - Development (sin auth)
    Route::get('/articles', [ArticleDevController::class, 'index'])->name('dev.articles.index');
    Route::get('/articles/{article}/edit-image', [ArticleDevController::class, 'editImage'])->name('dev.articles.edit-image');
    Route::post('/articles/{article}/update-image', [ArticleDevController::class, 'updateImage'])->name('dev.articles.update-image');
    Route::get('/articles/{article}/extract-source', [ArticleDevController::class, 'extractFromSource'])->name('dev.articles.extract-source');
});

// Placeholder dinámico - imagen única por slug (picsum.photos vía proxy)
Route::get('/placeholder-img', function (\Illuminate\Http\Request $request) {
    $slug = $request->query('s', 'default');
    $seed = abs(crc32($slug)) % 1000;
    $url = "https://picsum.photos/seed/{$seed}/1200/630";
    $cacheKey = 'placeholder_' . md5($slug);
    return \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($url) {
        $r = \Illuminate\Support\Facades\Http::timeout(10)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; DiarioVirtual/1.0)'])
            ->get($url);
        if (!$r->successful()) {
            abort(404);
        }
        return response($r->body(), 200)
            ->header('Content-Type', $r->header('Content-Type', 'image/jpeg'))
            ->header('Cache-Control', 'public, max-age=86400');
    });
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
