<?php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    echo json_encode([
        'step1' => 'vendor loaded',
        'memory' => memory_get_usage(),
        'time' => microtime(true)
    ]);
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    echo json_encode([
        'step2' => 'bootstrap loaded',
        'memory' => memory_get_usage(),
        'time' => microtime(true)
    ]);
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo json_encode([
        'step3' => 'kernel created',
        'memory' => memory_get_usage(),
        'time' => microtime(true)
    ]);
    
    $kernel->bootstrap();
    
    echo json_encode([
        'step4' => 'kernel bootstrapped',
        'memory' => memory_get_usage(),
        'time' => microtime(true)
    ]);
    
    $count = App\Models\Article::count();
    
    echo json_encode([
        'step5' => 'database accessed',
        'count' => $count,
        'memory' => memory_get_usage(),
        'time' => microtime(true)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'memory' => memory_get_usage(),
        'time' => microtime(true)
    ]);
}
?>
