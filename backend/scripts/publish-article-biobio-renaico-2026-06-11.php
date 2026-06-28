<?php
/**
 * Publica una noticia manual con formato DiarioVirtual (fuente externa + atribución).
 *
 *   php scripts/publish-article-biobio-renaico-2026-06-11.php
 *   php scripts/publish-article-biobio-renaico-2026-06-11.php --apply
 */
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Str;

$apply = in_array('--apply', $argv, true);

if ($apply && ! \Illuminate\Support\Facades\Schema::hasColumn('articles', 'metadata')) {
    \Illuminate\Support\Facades\Schema::table('articles', function ($table) {
        $table->json('metadata')->nullable();
    });
    echo "Columna metadata añadida a articles.\n";
}

$sourceUrl = 'https://www.biobiochile.cl/noticias/nacional/region-de-la-araucania/2026/06/11/sujetos-se-graban-perforando-lobulo-de-oreja-a-ancino-y-luego-atacan-a-personal-municipal-de-renaico.shtml';
$sourceName = 'BioBioChile';
$sourceHash = hash('sha256', $sourceUrl);

$title = '🚨 Sujetos se graban perforando lóbulo de oreja a anciano y luego atacan a personal municipal de Renaico';
$slug = 'sujetos-se-graban-perforando-lobulo-de-oreja-a-anciano-y-luego-atacan-a-personal-municipal-de-renaico';

$excerpt = 'Dos hombres quedaron en prisión preventiva tras una violenta jornada en Renaico: agredieron a funcionarios municipales y, en otro hecho, sometieron a dos personas en un domicilio del sector La Feria, incluyendo un adulto mayor.';

$content = <<<'TXT'
Dos hombres quedaron en prisión preventiva tras una jornada de extrema violencia en Renaico, en la provincia de Malleco. La Fiscalía Regional de La Araucanía formalizó a Francisco Alejandro Figueroa Montanares y Sergio Fernando Urra Monsalve por delitos de homicidio calificado frustrado, con agravantes de alevosía y ensañamiento, entre otros cargos.

Los hechos se remontan al sábado 30 de mayo. En primer lugar, un sujeto atacó con hacha y cuchillo a cuatro funcionarios municipales que desmontaban un escenario en el recinto Estación, dejando a varios con lesiones. Tras el incidente, el municipio anunció la presentación de una querella contra quienes resulten responsables.

En una segunda secuencia, según la investigación de la Fiscalía con apoyo de la Brigada de Investigación Criminal (Bicrim), los imputados habrían llevado engañadas a dos víctimas a un domicilio en el sector La Feria. Allí las sometieron a golpes y lesiones con objetos contundentes y armas cortantes.

Una de las víctimas es un adulto mayor. En el lugar, los agresores habrían perforado el lóbulo de su oreja con un taladro inalámbrico e intentado una agresión mayor contra su cabeza. El hecho fue registrado en video por los propios atacantes, según consta en la indagatoria. Producto de la brutalidad del ataque, uno de los afectados estuvo en riesgo vital y el otro sufrió fracturas y heridas en distintas partes del cuerpo.

En audiencia de formalización, el Juzgado de Garantía decretó prisión preventiva para ambos, al estimar que su libertad representa un peligro para la seguridad de la sociedad. La investigación quedó con un plazo de tres meses.

---

📰 **Agregador de Noticias - Provincia de Malleco**

📝 Fuente: BioBioChile | 🔗 Leer noticia original
TXT;

$payload = [
    'title' => $title,
    'slug' => $slug,
    'source_hash' => $sourceHash,
    'excerpt' => Str::limit($excerpt, 252),
    'content' => $content,
    'image_url' => 'https://api.diariozonasur.cl/images/renaico-ataque-biobio-2026-06-11.jpg',
    'is_external' => true,
    'external_url' => $sourceUrl,
    'status' => 'published',
    'published_at' => '2026-06-11 10:49:00',
    'metadata' => [
        'original_source' => $sourceName,
        'original_url' => $sourceUrl,
        'transformed_at' => now()->toIso8601String(),
        'authors' => 'Daniela Salgado · Carlos Martínez',
        'region' => 'La Araucanía',
        'comuna' => 'Renaico',
    ],
];

$existing = Article::query()->where('source_hash', $sourceHash)->orWhere('slug', $slug)->first();

echo $apply ? "=== APLICAR publicación ===\n" : "=== DRY-RUN ===\n";
echo "Título: {$title}\n";
echo "Slug: {$slug}\n";
echo "Fuente: {$sourceName}\n";
echo "URL: {$sourceUrl}\n";

if ($existing) {
    echo "\nYa existe artículo #{$existing->id} ({$existing->slug})\n";
    if ($apply) {
        $existing->fill($payload);
        $existing->save();
        echo "Actualizado.\n";
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
