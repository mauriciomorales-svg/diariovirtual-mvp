<?php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "Starting database query...\n";
    
    $articles = App\Models\Article::published()
        ->orderBy('published_at', 'desc')
        ->take(5)
        ->get();
    
    echo "Found " . $articles->count() . " articles\n";
    
    foreach ($articles as $article) {
        echo "Article: " . $article->title . "\n";
    }
    
    $data = [];
    foreach ($articles as $article) {
        $data[] = [
            'title' => $article->title ?: 'Sin título',
            'slug' => $article->slug ?: 'sin-slug',
            'excerpt' => $article->excerpt ?: 'Sin extracto',
            'content' => $article->content ?: 'Contenido no disponible. [NATIVE_AD_PLACEHOLDER]',
            'image_url' => $article->image_url ?: 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco',
            'published_at' => $article->published_at,
            'is_external' => $article->is_external ?: false,
            'external_url' => $article->external_url
        ];
    }
    
    $response = [
        'data' => $data,
        'total' => App\Models\Article::published()->count(),
        'source' => 'database_real_echo'
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo json_encode([
        'error' => $e->getMessage(),
        'message' => 'Failed to fetch real articles'
    ]);
}
?>
