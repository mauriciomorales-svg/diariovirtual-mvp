<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('output_buffering', 'Off');

echo "Starting...\n";

try {
    echo "Loading vendor...\n";
    require_once __DIR__ . '/../vendor/autoload.php';
    
    echo "Loading bootstrap...\n";
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    echo "Creating kernel...\n";
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "Bootstrapping...\n";
    $kernel->bootstrap();
    
    echo "Querying database...\n";
    $articles = App\Models\Article::published()
        ->orderBy('published_at', 'desc')
        ->take(5)
        ->get(['title', 'slug']);
    
    echo "Found " . $articles->count() . " articles\n";
    
    foreach ($articles as $article) {
        echo "- " . $article->title . "\n";
    }
    
    echo "Creating response...\n";
    $data = [];
    foreach ($articles as $article) {
        $data[] = [
            'title' => $article->title,
            'slug' => $article->slug
        ];
    }
    
    $response = [
        'data' => $data,
        'total' => $articles->count(),
        'source' => 'database_debug'
    ];
    
    echo "Encoding JSON...\n";
    $json = json_encode($response, JSON_UNESCAPED_UNICODE);
    
    echo "Output length: " . strlen($json) . "\n";
    echo $json;
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
