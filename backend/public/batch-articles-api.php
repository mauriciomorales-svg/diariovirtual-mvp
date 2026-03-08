<?php

// API para mostrar artículos recientes
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

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

    echo json_encode([
        'data' => $data,
        'current_page' => 1,
        'per_page' => 30,
        'total' => count($data),
        'last_page' => 1,
        'showing' => 'Mostrando ' . count($data) . ' noticias recientes',
    ]);
} catch (\Throwable $e) {
    echo json_encode([
        'error' => 'Error: ' . $e->getMessage(),
        'data' => [],
        'total' => 0,
    ]);
}
