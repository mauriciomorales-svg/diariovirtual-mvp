<?php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Test database connection
    $count = App\Models\Article::count();
    $published = App\Models\Article::published()->count();
    
    // Get one article
    $article = App\Models\Article::published()->first();
    
    echo json_encode([
        'status' => 'success',
        'total_articles' => $count,
        'published_articles' => $published,
        'sample_article' => [
            'title' => $article->title,
            'slug' => $article->slug,
            'has_content' => !empty($article->content),
            'has_excerpt' => !empty($article->excerpt),
            'published_at' => $article->published_at
        ],
        'memory' => memory_get_usage(),
        'time' => microtime(true)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
