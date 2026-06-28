<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;

$slugs = [
    'dos-hombres-prision-preventiva-ataque-taladro-renaico',
    'hallan-cuerpo-sin-vida-calle-angol-renaico',
    'renaico-querella-ataque-hacha-funcionarios-municipales',
    'hombre-hacha-terror-calles-renaico',
    'obras-nuevo-liceo-domingo-santa-maria-renaico',
    'alcalde-renaico-cuenta-publica-2025-concejo',
    'renaico-nueva-directiva-consejo-consultivo-2026',
    'influenza-aviar-traspatio-renaico-araucania',
    'fiscalia-pide-63-anos-exalcalde-renaico-reinao',
    'tribunal-canete-juicio-exalcalde-reinao-renaico',
];

foreach ($slugs as $slug) {
    $a = Article::where('slug', $slug)->first();
    if (! $a) {
        echo "MISSING: {$slug}\n";
        continue;
    }
    $img = $a->image_url ?? '';
    if (str_starts_with($img, 'http://api.diariozonasur.cl')) {
        $a->update(['image_url' => 'https://'.substr($img, 7)]);
        $img = $a->fresh()->image_url;
    }
    $ok = str_contains($img, 'api.diariozonasur.cl/images/') && ! str_contains($img, 'placeholder');
    echo ($ok ? 'OK' : 'NO')." | {$slug}\n  {$img}\n\n";
}
