<?php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Get real articles with proper data
    $articles = App\Models\Article::published()
        ->orderBy('published_at', 'desc')
        ->take(20)
        ->get();
    
    $data = [];
    foreach ($articles as $article) {
        $data[] = [
            'title' => $article->title ?: 'Sin título',
            'slug' => $article->slug ?: 'sin-slug',
            'excerpt' => $article->excerpt ?: 'Sin extracto',
            'content' => $article->content ?: 'Contenido no disponible. [NATIVE_AD_PLACEHOLDER] Este es un artículo de Diario Malleco.',
            'image_url' => $article->image_url ?: 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco',
            'published_at' => $article->published_at,
            'is_external' => $article->is_external ?: false,
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
        'source' => 'database_real_working'
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'message' => 'Failed to fetch real articles'
    ]);
}
?>
