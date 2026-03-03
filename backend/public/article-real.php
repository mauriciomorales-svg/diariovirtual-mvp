<?php
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $slug = $_GET['slug'] ?? 'carmona-llama-a-su-sector-a-tener-una-reaccion-mas-potente-ante-escalada-militar-en-medio-oriente';
    
    // Get single real article
    $article = App\Models\Article::published()
        ->where('slug', $slug)
        ->first();
    
    if (!$article) {
        http_response_code(404);
        echo json_encode(['error' => 'Article not found']);
        exit;
    }
    
    $response = [
        'title' => $article->title,
        'slug' => $article->slug,
        'excerpt' => $article->excerpt ?: 'Sin extracto disponible',
        'content' => $article->content ?: 'Contenido no disponible. [NATIVE_AD_PLACEHOLDER] Este es un artículo de Diario Malleco.',
        'image_url' => $article->image_url ?: 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco',
        'published_at' => $article->published_at,
        'is_external' => (bool)$article->is_external,
        'external_url' => $article->external_url
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'message' => 'Failed to fetch real article'
    ]);
}
?>
