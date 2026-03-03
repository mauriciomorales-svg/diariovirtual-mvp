<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ImageProxyController;

Route::middleware('api')->group(function () {
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{slug}', [ArticleController::class, 'show']);
    Route::get('image-proxy/{url}', [ImageProxyController::class, 'proxy']);
});
