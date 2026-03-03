<?php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Get first 20 real articles
    $articles = App\Models\Article::published()
        ->orderBy('published_at', 'desc')
        ->take(20)
        ->get(['title', 'slug', 'excerpt', 'content', 'image_url', 'published_at', 'is_external', 'external_url']);
    
    $data = [];
    foreach ($articles as $article) {
        $data[] = [
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'image_url' => $article->image_url,
            'published_at' => $article->published_at,
            'is_external' => $article->is_external,
            'external_url' => $article->external_url
        ];
    }
    
    $response = [
        'data' => $data,
        'current_page' => 1,
        'per_page' => 20,
        'total' => App\Models\Article::published()->count(),
        'last_page' => 1,
        'showing' => "Showing 1 to " . min(20, App\Models\Article::published()->count()) . " articles",
        'source' => 'database_real_simple'
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'message' => 'Failed to fetch real articles',
        'trace' => $e->getTraceAsString()
    ]);
}
?>
