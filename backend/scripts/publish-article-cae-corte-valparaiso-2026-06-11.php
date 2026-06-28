<?php
/**
 * Noticia nacional: Corte de Valparaíso frena cobro de CAE por la TGR.
 *
 *   php scripts/publish-article-cae-corte-valparaiso-2026-06-11.php
 *   php scripts/publish-article-cae-corte-valparaiso-2026-06-11.php --apply
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

$sourceUrl = 'https://www.biobiochile.cl/noticias/nacional/region-de-valparaiso/2026/06/11/pese-a-lo-informado-por-tgr-corte-de-valparaiso-acoge-recurso-y-deja-sin-efecto-cobro-de-cae.shtml';
$sourceName = 'BioBioChile';
$sourceHash = hash('sha256', $sourceUrl);

$title = 'Corte de Valparaíso anula cobro de la TGR a deudores del CAE: fallo dividido reabre debate nacional';
$slug = 'corte-valparaiso-anula-cobro-tgr-deudores-cae-2026';

$excerpt = 'La Tercera Sala acogió un recurso de protección y dejó sin efecto la ejecución tributaria del crédito universitario. Otra sala de la misma corte rechazó cuatro causas similares por vía distinta.';

$content = <<<'HTML'
<p>En un capítulo que tensa la relación entre miles de exestudiantes endeudados y el Fisco, la <strong>Corte de Apelaciones de Valparaíso</strong> acogió este jueves 11 de junio un recurso de protección contra la <strong>Tesorería General de la República (TGR)</strong> y dejó <strong>sin efecto el cobro forzado del Crédito con Aval del Estado (CAE)</strong> para la persona que presentó la acción.</p>

<p>El fallo —adoptado en la <strong>Tercera Sala</strong> y no unánime— sostiene que estas deudas <strong>no pueden perseguirse con las herramientas del cobro tributario</strong> y que tratarlas como una obligación fiscal ordinaria vulnera la <strong>igualdad ante la ley</strong>.</p>

<h2>¿Qué dice el tribunal?</h2>

<p>Los jueces recuerdan que el CAE nació como una vía para que jóvenes <strong>sin recursos económicos</strong> accedan a la educación superior. Por eso, argumentan, el crédito tiene una <strong>naturaleza especial</strong>: no es comparable a un impuesto ni a una deuda comercial que el Estado embarga con el mismo procedimiento que usa contra contribuyentes morosos.</p>

<p>“De no existir esta modalidad, muchas personas simplemente no podrían estudiar”, resume la resolución. En ese marco, ejecutar el CAE como deuda tributaria —con bloqueos, retenciones y el resto del arsenal de la TGR— sería, a juicio de la sala, desproporcionado y contrario a la garantía constitucional de trato igualitario.</p>

<h2>No todos los recursos prosperaron</h2>

<p>El escenario judicial es mixto. La <strong>Cuarta Sala</strong> de la misma corte rechazó, de forma unánime, <strong>cuatro recursos de protección</strong> que pedían el mismo objetivo: frenar el cobro del CAE.</p>

<p>En esos casos, las juezas estimaron que definir la naturaleza jurídica del crédito y el procedimiento válido para cobrarlo <strong>excede lo que puede resolver un recurso de protección</strong>, acción cautelar y no un juicio de fondo. Además, señalaron que no se acreditó un “derecho indubitado” que permitiera sacar a los recurrentes del procedimiento iniciado por la Tesorería.</p>

<h2>¿Qué implica para los deudores del CAE?</h2>

<p>El fallo favorable afecta al caso concreto que llegó a la Tercera Sala; <strong>no es una amnistía automática</strong> para todos quienes tienen CAE en Chile. Sin embargo, reabre un debate que cruza generaciones: más de un millón de personas arrastran deudas estudiantiles y la TGR ha intensificado cobros en los últimos años.</p>

<p>Para quienes viven en la Zona Sur —donde el acceso a la educación superior sigue siendo un desafío en comunas rurales y ciudades intermedias— la discusión no es abstracta: muchas familias de La Araucanía, Malleco y el resto del sur financiaron carreras técnicas o universitarias solo gracias al aval estatal.</p>

<h2>La postura de la TGR</h2>

<p>Horas antes de conocerse este revés judicial, la propia Tesorería había destacado otro fallo de la Corte de Valparaíso en sentido contrario y subrayado que los embargos por CAE se ajustan a la ley. El choque de criterios entre salas de un mismo tribunal muestra que la batalla legal continúa <strong>caso a caso</strong>.</p>

<p><small><em>Fuente: BioBioChile · <a href="https://www.biobiochile.cl/noticias/nacional/region-de-valparaiso/2026/06/11/pese-a-lo-informado-por-tgr-corte-de-valparaiso-acoge-recurso-y-deja-sin-efecto-cobro-de-cae.shtml" target="_blank" rel="noopener noreferrer">Leer noticia original</a></em></small></p>
HTML;

$now = now();

$payload = [
    'title' => $title,
    'slug' => $slug,
    'source_hash' => $sourceHash,
    'excerpt' => Str::limit($excerpt, 252),
    'content' => $content,
    'image_url' => 'https://via.placeholder.com/1200x630/1e3a5f/ffffff?text=CAE+%C2%B7+TGR',
    'is_external' => true,
    'external_url' => $sourceUrl,
    'status' => 'published',
    'published_at' => $now->format('Y-m-d H:i:s'),
    'metadata' => [
        'original_source' => $sourceName,
        'original_url' => $sourceUrl,
        'transformed_at' => $now->toIso8601String(),
        'authors' => 'Diario Zona Sur',
        'region' => 'Nacional',
        'categoria' => 'nacional',
        'tema' => 'economia',
    ],
];

$existing = Article::query()->where('source_hash', $sourceHash)->orWhere('slug', $slug)->first();

echo $apply ? "=== APLICAR publicación ===\n" : "=== DRY-RUN ===\n";
echo "Título: {$title}\n";
echo "Slug: {$slug}\n";

if ($existing) {
    echo "\nYa existe artículo #{$existing->id} ({$existing->slug})\n";
    if ($apply) {
        $existing->fill($payload);
        $existing->touch();
        $existing->save();
        echo "Actualizado (created_at renovado).\n";
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
