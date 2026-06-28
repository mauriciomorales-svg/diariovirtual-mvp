<?php
/**
 * Descarga imágenes og:source para artículos del lote Renaico 2026.
 *
 *   php scripts/refresh-renaico-lote-images.php --apply
 */
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use App\Services\ImageExtractorService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

$apply = in_array('--apply', $argv, true);
$slugs = [
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

/** Imagen manual cuando la fuente no tiene og:image */
$manualImages = [
    'obras-nuevo-liceo-domingo-santa-maria-renaico' => 'https://www.eldiariodelaaraucania.cl/wp-content/uploads/2026/04/liceo-domingo-santa-maria-renaico-obras.jpg',
];

function downloadAndSave(string $url, string $basename): ?string
{
    try {
        $response = Http::timeout(15)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; DiarioVirtual/1.0)',
                'Accept' => 'image/*',
            ])
            ->get($url);
        if (! $response->successful()) {
            return null;
        }
        $manager = new ImageManager(new Driver());
        $image = $manager->read($response->body())->scaleDown(1200, 630);
        $filename = 'images/'.$basename.'.jpg';
        $path = public_path($filename);
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $image->toJpeg(85)->save($path);

        return 'https://api.diariozonasur.cl/'.$filename;
    } catch (\Throwable $e) {
        echo '  error: '.$e->getMessage()."\n";

        return null;
    }
}

function scrapeFirstImage(string $pageUrl): ?string
{
    try {
        $html = Http::timeout(12)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($pageUrl)->body();
        if (preg_match('/wp-content\/uploads\/[^"\']+\.(jpg|jpeg|png|webp)/i', $html, $m)) {
            return 'https://www.eldiariodelaaraucania.cl/'.$m[0];
        }
    } catch (\Throwable) {
    }

    return null;
}

$extractor = app(ImageExtractorService::class);

echo $apply ? "=== APLICAR imágenes lote Renaico ===\n\n" : "=== DRY-RUN imágenes ===\n\n";

foreach ($slugs as $slug) {
    $article = Article::where('slug', $slug)->first();
    if (! $article) {
        echo "✗ No encontrado: {$slug}\n\n";
        continue;
    }

    if (str_contains($article->image_url ?? '', 'api.diariozonasur.cl/images/') && ! str_contains($article->image_url, 'via.placeholder')) {
        echo "○ Ya tiene imagen local: {$slug}\n";
        echo "  {$article->image_url}\n\n";
        continue;
    }

    $imgUrl = $manualImages[$slug] ?? null;
    if (! $imgUrl && $article->external_url) {
        $imgUrl = $extractor->extractFromUrl($article->external_url);
    }
    if (! $imgUrl && $article->external_url) {
        $imgUrl = scrapeFirstImage($article->external_url);
    }

    echo ($imgUrl ? '→' : '✗')." {$article->title}\n";
    echo "  slug: {$slug}\n";

    if (! $imgUrl) {
        echo "  sin imagen disponible\n\n";
        continue;
    }

    echo "  origen: {$imgUrl}\n";

    if (! $apply) {
        echo "\n";
        continue;
    }

    $local = downloadAndSave($imgUrl, $slug);
    if ($local) {
        $article->update(['image_url' => $local]);
        echo "  guardada: {$local}\n\n";
    } else {
        echo "  falló descarga\n\n";
    }

    usleep(300000);
}

if (! $apply) {
    echo "Dry-run OK. Usa --apply.\n";
}
