<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;

$slug = $argv[1] ?? 'renaico-trampa-invisible-juventud-drogas-salud-mental';

$article = Article::where('slug', $slug)->first();
if (!$article) {
    echo "No encontrado: {$slug}\n";
    exit(1);
}

echo "=== NOTICIA ===\n";
echo "Título: {$article->title}\n";
echo "Slug: {$article->slug}\n";
echo "Visitas: " . (int) $article->view_count . "\n";
echo "Última visita: " . ($article->last_viewed_at ? $article->last_viewed_at->format('Y-m-d H:i:s') : '—') . "\n";
echo "Publicada: " . ($article->published_at ? $article->published_at->format('Y-m-d H:i:s') : '—') . "\n";

echo "\n=== TOP 5 NOTICIAS ===\n";
$top = Article::where('status', 'published')
    ->orderByDesc('view_count')
    ->limit(5)
    ->get(['title', 'slug', 'view_count', 'last_viewed_at']);

foreach ($top as $i => $t) {
    $last = $t->last_viewed_at ? $t->last_viewed_at->format('d/m H:i') : '—';
    echo ($i + 1) . ". {$t->view_count} visitas | {$last} | {$t->slug}\n";
}

echo "\nTotal visitas sitio: " . (int) Article::sum('view_count') . "\n";
echo "Noticias publicadas: " . Article::where('status', 'published')->count() . "\n";
