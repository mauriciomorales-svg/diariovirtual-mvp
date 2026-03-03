<?php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $page = $_GET['page'] ?? 1;
    $perPage = 20;
    
    // Get REAL articles from database
    $articles = App\Models\Article::published()
        ->orderBy('published_at', 'desc')
        ->paginate($perPage, ['*'], 'page', $page);
    
    $data = [];
    foreach ($articles->items() as $article) {
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
        'current_page' => $articles->currentPage(),
        'per_page' => $articles->perPage(),
        'total' => $articles->total(),
        'last_page' => $articles->lastPage(),
        'showing' => "Showing {$articles->firstItem()} to {$articles->lastItem()} of {$articles->total()} articles",
        'source' => 'database_real_articles'
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'message' => 'Failed to fetch real articles',
        'trace' => $e->getTraceAsString()
    ]);
}
?>
