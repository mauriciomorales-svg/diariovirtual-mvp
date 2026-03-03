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
    
    $response = [
        'data' => $articles->items(),
        'current_page' => $articles->currentPage(),
        'per_page' => $articles->perPage(),
        'total' => $articles->total(),
        'last_page' => $articles->lastPage(),
        'showing' => "Showing {$articles->firstItem()} to {$articles->lastItem()} of {$articles->total()} articles",
        'source' => 'database_real_data'
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'message' => 'Failed to fetch real articles from database',
        'trace' => $e->getTraceAsString()
    ]);
}
?>
