<?php
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Get real articles with all fields
    $articles = App\Models\Article::published()
        ->orderBy('published_at', 'desc')
        ->take(20)
        ->get();
    
    $data = [];
    foreach ($articles as $article) {
        $data[] = [
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt ?: 'Sin extracto disponible',
            'content' => $article->content ?: 'Contenido no disponible. [NATIVE_AD_PLACEHOLDER] Este es un artículo de Diario Malleco.',
            'image_url' => $article->image_url ?: 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco',
            'published_at' => $article->published_at,
            'is_external' => (bool)$article->is_external,
            'external_url' => $article->external_url
        ];
    }
    
    $response = [
        'data' => $data,
        'current_page' => 1,
        'per_page' => 20,
        'total' => App\Models\Article::published()->count(),
        'last_page' => 1,
        'showing' => "Showing 1 to " . min(20, App\Models\Article::published()->count()) . " of " . App\Models\Article::published()->count() . " articles",
        'source' => 'database_real_success'
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'message' => 'Failed to fetch real articles'
    ]);
}
?>
