<?php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Use Laravel's ArticleController directly
    $controller = new App\Http\Controllers\Api\ArticleController();
    $request = Illuminate\Http\Request::capture();
    
    $response = $controller->index($request);
    
    echo $response->getContent();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'message' => 'Failed to use Laravel ArticleController',
        'trace' => $e->getTraceAsString()
    ]);
}
?>
