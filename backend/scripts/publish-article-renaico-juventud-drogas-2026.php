<?php
/**
 * Reportaje Renaico: juventud, drogas, salud mental — v3 con gráficos SVG.
 *
 *   php scripts/publish-article-renaico-juventud-drogas-2026.php
 *   php scripts/publish-article-renaico-juventud-drogas-2026.php --apply
 */
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Str;

$apply = in_array('--apply', $argv, true);

$sourceHash = hash('sha256', 'diario-zona-sur-reportaje-renaico-juventud-drogas-2026-v3');

$title = 'La trampa invisible de las drogas: un llamado a la conciencia de la juventud en Renaico';
$slug = 'renaico-trampa-invisible-juventud-drogas-salud-mental';

$excerpt = 'Reportaje visual: 5 gráficos SENDA, testimonio anónimo, línea de tiempo Renaico 2026 y 10 datos para que los jóvenes de Malleco se cuiden.';

$htmlPath = __DIR__.'/../../data-municipalidad-renaico/CONTENIDO-HTML-RENAICO-JUVENTUD-DROGAS-v3.html';
if (! is_readable($htmlPath)) {
    echo "No se encuentra: {$htmlPath}\n";
    exit(1);
}
$content = file_get_contents($htmlPath);

$imageUrl = 'https://api.diariozonasur.cl/images/renaico-puente-rio-hero-2026.jpg?v=2';

$now = now();

$payload = [
    'title' => $title,
    'slug' => $slug,
    'source_hash' => $sourceHash,
    'excerpt' => Str::limit($excerpt, 252),
    'content' => $content,
    'image_url' => $imageUrl,
    'is_external' => false,
    'external_url' => null,
    'status' => 'published',
    'published_at' => $now->format('Y-m-d H:i:s'),
    'metadata' => [
        'original_source' => 'Diario Zona Sur',
        'authors' => 'Diario Zona Sur',
        'region' => 'La Araucanía',
        'comuna' => 'Renaico',
        'categoria' => 'renaico',
        'tipo' => 'reportaje',
        'tema' => 'salud_publica',
        'visualizaciones' => 'graficos-svg,kpi,testimonio,timeline',
    ],
];

$existing = Article::query()->where('slug', $slug)->first();

echo $apply ? "=== APLICAR publicación v3 ===\n" : "=== DRY-RUN v3 ===\n";
echo "Título: {$title}\n";
echo "Slug: {$slug}\n";
echo 'Contenido: '.strlen($content)." bytes\n";

if ($existing) {
    echo "\nYa existe artículo #{$existing->id}\n";
    if ($apply) {
        $existing->fill($payload);
        $existing->touch();
        $existing->save();
        echo "Actualizado con gráficos y testimonio.\n";
        echo "Web: https://diariozonasur.cl/{$slug}\n";
    }
    exit(0);
}

if (! $apply) {
    echo "\nDry-run OK. Usa --apply para publicar.\n";
    exit(0);
}

$article = Article::create($payload);
echo "\nCreado artículo #{$article->id}\n";
echo "Web: https://diariozonasur.cl/{$slug}\n";
