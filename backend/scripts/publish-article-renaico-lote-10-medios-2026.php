<?php
/**
 * Publica 10 noticias de Renaico (otros medios) con redacción propia Diario Zona Sur.
 *
 *   php scripts/publish-article-renaico-lote-10-medios-2026.php
 *   php scripts/publish-article-renaico-lote-10-medios-2026.php --apply
 */
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

$apply = in_array('--apply', $argv, true);

if ($apply && ! Schema::hasColumn('articles', 'metadata')) {
    Schema::table('articles', function ($table) {
        $table->json('metadata')->nullable();
    });
    echo "Columna metadata añadida a articles.\n";
}

$placeholder = 'https://via.placeholder.com/1200x630/1a365d/ffffff?text=Renaico';

$articles = [
    [
        'title' => '🚨 Dos hombres en prisión preventiva tras ataque brutal con taladro en Renaico',
        'slug' => 'dos-hombres-prision-preventiva-ataque-taladro-renaico',
        'excerpt' => 'Grabaron el momento en que perforaron la oreja de un anciano; uno de los heridos estuvo en riesgo vital.',
        'published_at' => '2026-06-11 11:00:00',
        'source_name' => 'BioBioChile',
        'source_url' => 'https://www.biobiochile.cl/noticias/nacional/region-de-la-araucania/2026/06/11/sujetos-se-graban-perforando-lobulo-de-oreja-a-ancino-y-luego-atacan-a-personal-municipal-de-renaico.shtml',
        'image_url' => 'https://api.diariozonasur.cl/images/renaico-ataque-biobio-2026-06-11.jpg',
        'body' => <<<'TXT'
Francisco Alejandro Figueroa Montanares y Sergio Fernando Urra Monsalve enfrentan prisión preventiva por una seguidilla de ataques ocurridos el 30 de mayo en Renaico. Primero agredieron con hacha y cuchillo a funcionarios municipales que desmontaban un escenario en el Recinto Estación. Luego trasladaron a dos víctimas a una vivienda, donde las golpearon y perforaron el lóbulo de una oreja con un taladro —todo filmado por los propios agresores—. Uno de los afectados quedó en riesgo vital. La Fiscalía los formalizó por homicidio frustrado con agravantes.
TXT,
    ],
    [
        'title' => '🚨 Hallan cuerpo sin vida en plena calle Angol de Renaico',
        'slug' => 'hallan-cuerpo-sin-vida-calle-angol-renaico',
        'excerpt' => 'PDI investiga posible intervención de terceros.',
        'published_at' => '2026-06-11 09:30:00',
        'source_name' => 'BioBioChile',
        'source_url' => 'https://www.biobiochile.cl/noticias/nacional/region-de-la-araucania/2026/06/11/pdi-investiga-el-hallazgo-de-un-hombre-muerto-en-la-via-publica-en-renaico.shtml',
        'body' => <<<'TXT'
Personal de Carabineros, Fiscalía y la Brigada de Homicidios de la PDI encontraron el cuerpo de un hombre en la calle Angol. Las primeras diligencias apuntan a que podría tratarse de un homicidio. El caso quedó en manos de la Policía de Investigaciones de Temuco.
TXT,
    ],
    [
        'title' => '🚨 Renaico presenta querella tras ataque con hacha a funcionarios municipales',
        'slug' => 'renaico-querella-ataque-hacha-funcionarios-municipales',
        'excerpt' => 'El agresor huyó tras herir a un trabajador durante el desmonte de un escenario.',
        'published_at' => '2026-06-02 12:00:00',
        'source_name' => 'BioBioChile',
        'source_url' => 'https://www.biobiochile.cl/noticias/nacional/region-de-la-araucania/2026/06/02/municipio-de-renaico-interpone-querella-por-agresion-contra-funcionarios-fueron-atacados-con-hacha.shtml',
        'body' => <<<'TXT'
La Municipalidad de Renaico interpuso una querella penal luego de que un hombre armado con un hacha atacara a funcionarios que retiraban un escenario en el Recinto Estación. Una persona resultó herida y el agresor escapó. La Fiscalía de Angol instruyó a la PDI para dar con su paradero.
TXT,
    ],
    [
        'title' => '🚨 Hombre armado con hacha siembra el terror en las calles de Renaico',
        'slug' => 'hombre-hacha-terror-calles-renaico',
        'excerpt' => 'Al menos dos personas heridas en ataques sin provocación; amplio operativo policial.',
        'published_at' => '2026-05-31 08:00:00',
        'source_name' => 'BioBioChile',
        'source_url' => 'https://www.biobiochile.cl/noticias/nacional/region-de-la-araucania/2026/05/31/noche-de-terror-en-renaico-hombre-ataco-con-hacha-a-tres-funcionarios-publicos-y-dejo-dos-heridos.shtml',
        'body' => <<<'TXT'
Un sujeto recorrió varias calles de Renaico atacando con un hacha a tres personas, entre ellas funcionarios municipales. Dos resultaron lesionadas. Carabineros revisó grabaciones de cámaras de seguridad para identificarlo. El hecho generó alarma en la comuna.
TXT,
    ],
    [
        'title' => 'Comienzan las obras del nuevo Liceo Domingo Santa María en Renaico',
        'slug' => 'obras-nuevo-liceo-domingo-santa-maria-renaico',
        'excerpt' => 'Inversión de más de $17.800 millones beneficiará a 608 estudiantes.',
        'published_at' => '2026-04-11 10:00:00',
        'source_name' => 'El Diario de la Araucanía',
        'source_url' => 'https://www.eldiariodelaaraucania.cl/2026/04/11/liceo-domingo-santa-maria-renaico-obras-educacion/',
        'image_url' => 'https://api.diariozonasur.cl/images/renaico-liceo-domingo-santa-maria-2026.jpg',
        'body' => <<<'TXT'
El Gobierno Regional de La Araucanía inició la construcción del nuevo Liceo Domingo Santa María. El proyecto contempla 4.415 m² de infraestructura moderna. El alcalde Claudio Musre señaló que la comunidad esperaba esta obra desde hace más de una década. El gobernador René Saffirio pidió corresponsabilidad de las familias en la formación de los estudiantes.
TXT,
    ],
    [
        'title' => 'Alcalde de Renaico rinde Cuenta Pública 2025 ante el Concejo y la comunidad',
        'slug' => 'alcalde-renaico-cuenta-publica-2025-concejo',
        'excerpt' => 'Más de 200 personas asistieron a la ceremonia; se destacó el rol del mundo rural.',
        'published_at' => '2026-04-29 18:00:00',
        'source_name' => 'Municipalidad de Renaico',
        'source_url' => 'https://municipalidadrenaico.cl/alcalde-claudio-musre-rindio-impecable-cuenta-publica-al-concejo-municipal-y-a-la-comunidad/',
        'body' => <<<'TXT'
Claudio Musre Contreras presentó la Cuenta Pública del período 2025 en una ceremonia que reunió a más de 200 personas. El alcalde expuso ingresos, gastos y principales gestiones del año. Durante el acto se rindió homenaje a la funcionaria fallecida Jannette Zerené Pavez y se reconoció la labor del sector campesino en el desarrollo de la comuna.
TXT,
    ],
    [
        'title' => 'Niños y adolescentes de Renaico eligen nueva directiva de su Consejo Consultivo',
        'slug' => 'renaico-nueva-directiva-consejo-consultivo-2026',
        'excerpt' => 'La Oficina Local de la Niñez organizó la votación en el Observatorio Regional de Cultura.',
        'published_at' => '2026-04-29 11:00:00',
        'source_name' => 'Municipalidad de Renaico',
        'source_url' => 'https://municipalidadrenaico.cl/consejo-consultivo-tiene-nueva-directiva-2026/',
        'body' => <<<'TXT'
La Oficina Local de la Niñez (OLN) realizó la elección de la nueva mesa directiva del Consejo Consultivo de Niños, Niñas y Adolescentes. La directora de DIDECO, Carolina Saavedra, acompañó el proceso. La instancia busca fortalecer la participación juvenil en las decisiones comunales.
TXT,
    ],
    [
        'title' => '🚨 Detectan primer caso de influenza aviar en aves de traspatio en La Araucanía',
        'slug' => 'influenza-aviar-traspatio-renaico-araucania',
        'excerpt' => 'El foco se registró en Renaico; se activó alerta preventiva regional.',
        'published_at' => '2026-03-20 09:00:00',
        'source_name' => 'BioBioChile',
        'source_url' => 'https://www.biobiochile.cl/noticias/nacional/region-de-la-araucania/2026/03/20/sag-confirma-primer-caso-de-influenza-aviar-en-aves-domesticas-en-la-araucania.shtml',
        'body' => <<<'TXT'
El Servicio Agrícola y Ganadero confirmó el primer caso de influenza aviar de alta patogenicidad H5N1 en un traspatio de Renaico. La detección activó Alerta Temprana Preventiva en toda la región. El SAG reforzó los controles de bioseguridad ante el retorno de aves migratorias.
TXT,
    ],
    [
        'title' => 'Fiscalía pide 63 años de cárcel para exalcalde de Renaico por delitos sexuales',
        'slug' => 'fiscalia-pide-63-anos-exalcalde-renaico-reinao',
        'excerpt' => 'Juan Carlos Reinao enfrenta juicio oral en Cañete por seis víctimas.',
        'published_at' => '2026-05-18 10:00:00',
        'source_name' => 'Las Noticias de Malleco',
        'source_url' => 'https://lasnoticiasdemalleco.cl/policial/exalcalde-de-renaico-a-juicio-en-mayo-fiscalia-pide-63-anos-de-presidio/',
        'body' => <<<'TXT'
El Ministerio Público solicitó una pena acumulada de hasta 63 años de presidio para el exalcalde Juan Carlos Reinao. El juicio oral comenzó el 18 de mayo de 2026 en el Tribunal de Cañete. Se le imputan abusos sexuales, violación e inducción al aborto cometidos entre 2006 y 2020 contra seis mujeres, dos de ellas menores de edad al momento de los hechos. Se espera la declaración de 70 testigos.
TXT,
    ],
    [
        'title' => 'Tribunal de Cañete fija para mayo el juicio contra exalcalde Reinao',
        'slug' => 'tribunal-canete-juicio-exalcalde-reinao-renaico',
        'excerpt' => 'Acusado de abusos y violaciones; permanece en prisión preventiva desde 2023.',
        'published_at' => '2025-12-30 12:00:00',
        'source_name' => 'BioBioChile',
        'source_url' => 'https://www.biobiochile.cl/noticias/nacional/chile/2025/12/30/fijan-para-mayo-de-2026-el-juicio-contra-exalcalde-de-renaico-acusado-por-delitos-sexuales.shtml',
        'body' => <<<'TXT'
El Tribunal Oral de Cañete programó el inicio del juicio contra Juan Carlos Reinao para el 18 de mayo de 2026. El exalcalde de Renaico arriesga hasta 63 años de cárcel por delitos sexuales cometidos durante su gestión. La causa incluye seis víctimas y se prolongará aproximadamente un mes por la cantidad de testigos y pruebas documentales.
TXT,
    ],
];

function buildContent(string $body, string $sourceName, string $sourceUrl): string
{
    $paragraphs = array_filter(array_map('trim', preg_split('/\n\s*\n/', $body)));
    $html = '';
    foreach ($paragraphs as $p) {
        $html .= '<p>'.e($p).'</p>'."\n";
    }
    $html .= '<p><small><em>Fuente: '.e($sourceName).' · '
        .'<a href="'.e($sourceUrl).'" target="_blank" rel="noopener noreferrer">Leer noticia original</a></em></small></p>';

    return $html;
}

echo $apply ? "=== APLICAR lote 10 noticias Renaico ===\n\n" : "=== DRY-RUN lote 10 noticias Renaico ===\n\n";

$created = 0;
$updated = 0;

foreach ($articles as $item) {
    $sourceHash = hash('sha256', $item['source_url']);
    $content = buildContent($item['body'], $item['source_name'], $item['source_url']);

    $payload = [
        'title' => $item['title'],
        'slug' => $item['slug'],
        'source_hash' => $sourceHash,
        'excerpt' => Str::limit($item['excerpt'], 252),
        'content' => $content,
        'image_url' => $item['image_url'] ?? $placeholder,
        'is_external' => true,
        'external_url' => $item['source_url'],
        'status' => 'published',
        'published_at' => $item['published_at'],
        'metadata' => [
            'original_source' => $item['source_name'],
            'original_url' => $item['source_url'],
            'transformed_at' => now()->toIso8601String(),
            'authors' => 'Diario Zona Sur',
            'region' => 'La Araucanía',
            'comuna' => 'Renaico',
        ],
    ];

    $existing = Article::query()
        ->where('source_hash', $sourceHash)
        ->orWhere('slug', $item['slug'])
        ->first();

    echo ($existing ? '↻' : '+')." {$item['title']}\n";
    echo "  slug: {$item['slug']}\n";
    echo "  web: https://diariozonasur.cl/{$item['slug']}\n";

    if (! $apply) {
        echo "\n";
        continue;
    }

    if ($existing) {
        $existing->fill($payload);
        $existing->save();
        $updated++;
        echo "  → Actualizado #{$existing->id}\n\n";
    } else {
        $article = Article::create($payload);
        $created++;
        echo "  → Creado #{$article->id}\n\n";
    }
}

if ($apply) {
    echo "Listo: {$created} creadas, {$updated} actualizadas.\n";
} else {
    echo "Dry-run OK. Usa --apply para publicar.\n";
}
