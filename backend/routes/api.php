<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ImageProxyController;
use App\Http\Controllers\Admin\GeminiBatchImportController;
use App\Services\GeminiService;

Route::middleware('api')->group(function () {
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('article/by-id/{id}', [ArticleController::class, 'showById'])->where('id', '[0-9a-fA-F\-]{36}');
    Route::get('articles/{slug}', [ArticleController::class, 'show']);
    Route::get('image-proxy/{url}', [ImageProxyController::class, 'proxy']);
    Route::post('batch-parse', [GeminiBatchImportController::class, 'parseBatch']);
    Route::post('batch-import', [GeminiBatchImportController::class, 'processBatchImport']);
    Route::post('transform-article', [GeminiBatchImportController::class, 'transformArticle']);
    Route::get('gemini-health', function () {
        $health = app(GeminiService::class)->healthCheck();
        return response()->json([
            'healthy' => $health['available'],
            'model' => $health['model'] ?? 'unknown',
            'error' => $health['error'] ?? null,
            'quota_exceeded' => $health['quota_exceeded'] ?? false,
            'message' => $health['available']
                ? 'Conexión con Gemini OK'
                : ($health['quota_exceeded'] ?? false ? 'Cuota agotada' : ($health['error'] ?? 'Sin conexión')),
        ]);
    });
});
