<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;

$slug = $argv[1] ?? '';
$imageUrl = $argv[2] ?? '';

if ($slug === '' || $imageUrl === '') {
    fwrite(STDERR, "Uso: php update-article-image.php <slug> <image_url>\n");
    exit(1);
}

$article = Article::where('slug', $slug)->first();
if (! $article) {
    fwrite(STDERR, "Artículo no encontrado: {$slug}\n");
    exit(1);
}

$article->update(['image_url' => $imageUrl]);
echo "OK #{$article->id} → {$imageUrl}\n";
