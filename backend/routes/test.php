<?php

use Illuminate\Support\Facades\Route;
use App\Models\Article;

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
