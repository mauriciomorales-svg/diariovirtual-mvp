<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$url = 'https://www.eldiariodelaaraucania.cl/2026/04/11/liceo-domingo-santa-maria-renaico-obras-educacion/';
$html = Illuminate\Support\Facades\Http::timeout(15)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url)->body();
preg_match_all('/https?:\/\/[^"\']+\.(jpg|jpeg|png|webp)/i', $html, $m);
preg_match_all('/\/wp-content\/uploads\/[^"\']+\.(jpg|jpeg|png|webp)/i', $html, $m2);
echo "FULL URLS:\n";
foreach (array_unique($m[0] ?? []) as $u) {
    echo $u."\n";
}
echo "\nRELATIVE:\n";
foreach (array_unique($m2[0] ?? []) as $u) {
    echo $u."\n";
}
