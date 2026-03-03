<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking saved article...\n";
$article = App\Models\Article::latest()->first();
echo "Title: " . $article->title . "\n";
echo "Status: " . $article->status . "\n";
echo "Content preview: " . substr($article->content, 0, 100) . "...\n";
echo "External: " . ($article->is_external ? 'Yes' : 'No') . "\n";
echo "Published at: " . $article->published_at . "\n";
?>
