<?php
require_once '../../../vendor/autoload.php';

$app = require_once '../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// API endpoint for articles
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

use App\Models\Article;

try {
    $total = Article::where('status', 'published')->count();
    
    $articles = Article::where('status', 'published')
        ->orderBy('published_at', 'desc')
        ->take(100)
        ->get();

    echo json_encode([
        'success' => true,
        'data' => $articles,
        'count' => $articles->count(),
        'total' => $total,
        'showing' => 'Mostrando ' . $articles->count() . ' de ' . $total . ' noticias'
    ]);
} catch (\Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
