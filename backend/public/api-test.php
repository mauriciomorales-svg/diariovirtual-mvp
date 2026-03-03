<?php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $articles = App\Models\Article::published()
        ->orderBy('published_at', 'desc')
        ->take(5)
        ->get(['title', 'slug', 'excerpt']);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'API working correctly',
        'total_articles' => App\Models\Article::count(),
        'published_articles' => App\Models\Article::published()->count(),
        'sample_articles' => $articles,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
