<?php

// API para mostrar artículos recientes del batch import
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Usar conexión de Laravel
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    // Inicializar la aplicación Laravel
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Usar la conexión de Laravel
    $db = $app->make('db');
    $pdo = $db->connection()->getPdo();
    
    // Obtener últimos artículos del batch import
    $stmt = $pdo->prepare("
        SELECT title, slug, excerpt, content, image_url, published_at, is_external, external_url
        FROM articles 
        WHERE status = 'published' 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear respuesta
    $response = [
        'data' => array_map(function($article) {
            return [
                'title' => $article['title'],
                'slug' => $article['slug'],
                'excerpt' => $article['excerpt'],
                'content' => $article['content'],
                'image_url' => $article['image_url'],
                'published_at' => $article['published_at'],
                'is_external' => (bool)$article['is_external'],
                'external_url' => $article['external_url']
            ];
        }, $articles),
        'current_page' => 1,
        'per_page' => 10,
        'total' => count($articles),
        'last_page' => 1,
        'showing' => 'Showing latest ' . count($articles) . ' articles from batch import'
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error: ' . $e->getMessage(),
        'data' => [],
        'total' => 0
    ]);
}
?>
