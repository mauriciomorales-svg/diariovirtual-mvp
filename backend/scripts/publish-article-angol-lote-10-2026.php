<?php
/**
 * Publica 10 noticias de Angol (comuna) — Diario Zona Sur.
 *
 *   php scripts/publish-article-angol-lote-10-2026.php
 *   php scripts/publish-article-angol-lote-10-2026.php --apply
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

$placeholder = 'https://via.placeholder.com/1200x630/4a148c/ffffff?text=Angol';

$articles = [
    [
        'title' => '🚨 Encapuchados armados asaltan a cinco trabajadores forestales en Angol',
        'slug' => 'encapuchados-asaltan-trabajadores-forestales-angol',
        'excerpt' => 'La PDI investiga el robo en el Fundo Deuco; se llevaron camioneta, plantas de pino y equipos de comunicación.',
        'published_at' => '2026-05-31 10:00:00',
        'source_name' => 'BioBioChile',
        'source_url' => 'https://www.biobiochile.cl/noticias/nacional/region-de-la-araucania/2026/05/31/pdi-busca-a-grupo-armado-que-asalto-a-cinco-trabajadores-forestales-en-angol.shtml',
        'body' => <<<'TXT'
Un grupo de encapuchados armados interceptó una camioneta de Renacer Servicios Forestales en la Ruta R-230, en el Fundo Deuco de Angol, propiedad de Forestal Arauco. Eran alrededor de las 07:00 horas y viajaban cinco trabajadores.

Desde una camioneta Ford Ranger gris descendieron tres sujetos que obligaron a los trabajadores a bajar y permanecer en el suelo. Luego descargaron plantas de pino, retiraron radios y el GPS y huyeron con el vehículo de la empresa. No hubo disparos ni heridos.

La Fiscalía instruyó diligencias a la BIRO de la PDI. El delegado presidencial de Malleco, Víctor Manoli, indicó que el grupo podría estar vinculado a un asalto previo contra funcionarios municipales de Angol, en el que se robó una camioneta que luego fue recuperada.
TXT,
    ],
    [
        'title' => '🚨 Cuatro riñas violentas en un solo día en el Liceo Ballacey de Angol',
        'slug' => 'cuatro-rinas-liceo-ballacey-angol',
        'excerpt' => 'Diez alumnos y apoderados involucrados; Carabineros detuvo a un adulto y derivará el caso a la Fiscalía.',
        'published_at' => '2026-05-12 16:00:00',
        'source_name' => 'Malleco7',
        'source_url' => 'https://www.malleco7.cl/cuatro-violentas-rinas-este-martes-en-liceo-ballacey-de-angol/',
        'body' => <<<'TXT'
El Liceo Bicentenario Enrique Ballacey vivió una jornada crítica este martes, con cuatro riñas registradas poco antes de las 15:00 horas: tres entre estudiantes y una entre apoderados.

El comisario de Carabineros de Angol, mayor Fernando Quiñiñir, confirmó que participaron diez alumnos menores de 16 años —no detenidos, pero con denuncia a la Fiscalía— y apoderados, de los cuales uno quedó arrestado por agresión. Un estudiante tenía orden de arresto vigente.

El director Luis Gallardo activó protocolos de seguridad, pidió apoyo a Carabineros y Seguridad Pública, y anunció la aplicación del reglamento interno y de la Ley Aula Segura. En un comunicado llamó a la comunidad angolina a fortalecer el respeto y la sana convivencia.
TXT,
    ],
    [
        'title' => 'Demolerán caserón abandonado de Aldeas S.O.S. en Angol por riesgo vecinal',
        'slug' => 'demoleran-caseron-aldeas-sos-angol',
        'excerpt' => 'El inmueble en desuso fue ocupado irregularmente; un niño de 10 años fue perseguido con un cuchillo en abril.',
        'published_at' => '2026-05-10 12:00:00',
        'source_name' => 'Malleco7',
        'source_url' => 'https://www.malleco7.cl/por-seguridad-demoleran-caseron-en-desuso-de-aldeas-s-o-s-en-angol/',
        'body' => <<<'TXT'
El alcalde de Angol, Enrique Neira, y el subdirector nacional de Aldeas S.O.S., Eloy Bastías, acordaron demoler el antiguo caserón de la residencia juvenil en Jorge Prat con Austria, en estado de abandono y tomado por personas en situación de calle.

Vecinos denunciaron reiterados problemas de inseguridad. El episodio más grave ocurrió el 16 de abril, cuando un niño de 10 años que iba en bicicleta fue perseguido por un adulto que salió del inmueble portando un arma blanca. El menor logró escapar.

La medida incluye desalojo —plazo de 30 días—, demolición preventiva y cierre perimetral. Neira señaló que el municipio y el Concejo de Angol habían alertado antes por hechos que aumentaron la preocupación en el barrio.
TXT,
    ],
    [
        'title' => 'Gobernador busca aportes regionales para el hospital de Angol ante recorte de $15 mil millones',
        'slug' => 'gobernador-aportes-hospital-angol-recorte-salud',
        'excerpt' => 'Saffirio no logró reunión con directores de salud; Fenpruss advierte por atención a población vulnerable.',
        'published_at' => '2026-06-01 11:00:00',
        'source_name' => 'BioBioChile',
        'source_url' => 'https://www.biobiochile.cl/noticias/nacional/region-de-la-araucania/2026/06/01/gobernador-de-la-araucania-analiza-realizar-aportes-a-hospitales-por-recorte-presupuestario.shtml',
        'body' => <<<'TXT'
El gobernador René Saffirio analiza aportes del Gobierno Regional para contrarrestar un recorte de más de $15.000 millones al presupuesto de salud en La Araucanía, que afecta entre otros al hospital de Angol.

Convocó a directores de establecimientos de Temuco, Imperial, Villarrica, Pucón, Pitrufquén, Lautaro, Angol, Victoria y Traiguén, pero no asistieron; solo dialogó con la Fenpruss. El coordinador del gremio advirtió que el acceso a la atención se verá afectado, especialmente para quienes más dependen del sistema público en comunas como Angol.

Saffirio teme que los recortes impulsen la contratación de servicios privados y debiliten la red pública. Indicó que insistirá en obtener información de los hospitales para acordar apoyos con el Consejo Regional.
TXT,
    ],
    [
        'title' => 'Tribunal admite tercera querella contra ministro Mesa por sentencias ligadas a Angol',
        'slug' => 'querella-ministro-mesa-regimiento-husares-angol',
        'excerpt' => 'Los hechos de 1973 ocurrieron en el Regimiento Húsares de Angol; tres exuniformados fueron condenados por homicidios.',
        'published_at' => '2026-05-21 10:00:00',
        'source_name' => 'BioBioChile',
        'source_url' => 'https://www.biobiochile.cl/noticias/nacional/region-de-la-araucania/2026/05/21/tercera-querella-por-prevaricacion-contra-ministro-mesa-es-declarada-admisible-por-tribunal-de-temuco.shtml',
        'body' => <<<'TXT'
El Juzgado de Garantía de Temuco declaró admisible una tercera querella por prevaricación imprudente contra el ministro de la Corte de Apelaciones Álvaro Mesa Latorre, presentada por la abogada Carla Fernández.

La acción representa a Germán Ojeda Bennett, Carlos Bunster Medina y Alessandro Cartoni Pruzzo, condenados por homicidios calificados de Luis Raúl Cotal y Ricardo Gustavo Rioseco, ocurridos entre el 4 y el 5 de octubre de 1973 en Angol, en el contexto del Regimiento Húsares.

Mesa los sentenció a 19 años de presidio; la Corte de Apelaciones rebajó la pena a 15 años y un día. La jueza Marcia Castillo envió los antecedentes al Ministerio Público, que ya investiga otras querellas similares a cargo de la fiscal Tatiana Esquivel.
TXT,
    ],
    [
        'title' => 'Angol celebra tres días el Día del Patrimonio con actividades en toda la ciudad',
        'slug' => 'angol-dia-patrimonio-cultural-2026',
        'excerpt' => 'Club Aéreo, Centro Cultural, Cruz Roja y la plaza Las Siete Fundaciones abren sus puertas al público.',
        'published_at' => '2026-05-29 10:00:00',
        'source_name' => 'Malleco7',
        'source_url' => 'https://www.malleco7.cl/partio-fiesta-de-los-patrimonios-en-angol/',
        'body' => <<<'TXT'
El Departamento de Turismo, Cultura y Deporte de la Municipalidad de Angol organizó una amplia programación para el Día del Patrimonio Cultural, con actividades el viernes, sábado y domingo.

El Club Aéreo de Angol —82 años— exhibirá material audiovisual, aeronaves y el despiece de un motor. En el Centro Cultural habrá talleres de la OLN, presentación del Coro de Profesores —50 años—, cueca con Los Confines y lanzamiento del disco “Un verdadero amor” de Luna Cantautora.

En la Plaza Las Siete Fundaciones se montará la exposición “Mesa de Turismo de Angol”. La Cruz Roja filial, con 77 años en la capital de Malleco, abrirá sus instalaciones para mostrar su labor humanitaria. Autoridades invitaron a la comunidad angolina a participar de la fiesta patrimonial.
TXT,
    ],
    [
        'title' => 'Proyecto de más de $10 mil millones convertirá el antiguo hospital de Angol en biblioteca',
        'slug' => 'biblioteca-municipal-antiguo-hospital-angol',
        'excerpt' => 'El edificio es Monumento Histórico Nacional; el diseño se entregará en el segundo semestre de 2026.',
        'published_at' => '2026-04-26 11:00:00',
        'source_name' => 'Malleco7',
        'source_url' => 'https://www.malleco7.cl/impulsan-futura-biblioteca-en-antiguo-hospital-de-angol/',
        'body' => <<<'TXT'
Una inversión que supera los $10.000 millones busca habilitar el antiguo hospital de Angol como nueva Biblioteca Municipal. El proyecto está en etapa final de diseño y será entregado a las autoridades en el segundo semestre de 2026.

La propuesta fue presentada en el Centro Cultural de Angol. El inmueble será rehabilitado para uso público con tres salas de lectura, un salón multiuso y espacios culturales en dos niveles, bajo normas estrictas de conservación patrimonial.

El arquitecto Christian Yutronic expuso el plan junto a profesionales del MOP y la red de bibliotecas de La Araucanía. El alcalde Enrique Neira comprometió gestiones para acortar plazos en al menos un año; la consejera Mónica Rodríguez ofreció apoyo regional. Se estima un plazo mínimo de cinco años para la obra completa.
TXT,
    ],
    [
        'title' => 'Fashion Fest 2026 reunió a cerca de mil personas en el centro de Angol',
        'slug' => 'fashion-fest-2026-centro-angol',
        'excerpt' => '32 modelos locales —siete niñas— desfilaron en la calle Lautaro con boutiques y el Programa de la Mujer.',
        'published_at' => '2026-02-25 20:00:00',
        'source_name' => 'Malleco7',
        'source_url' => 'https://www.malleco7.cl/fashion-fest-fue-un-exito-en-angol/',
        'body' => <<<'TXT'
El Fashion Fest 2026 se realizó un sábado en la noche en una pasarela montada en calle Lautaro, entre la plaza Las Siete Fundaciones y la Delegación de Malleco en Angol. Asistieron cerca de mil personas.

Desfilaron 32 modelos angolinas, incluidas siete niñas, con prendas de las boutiques Gabantta, Andrea López, Edén y Vanidades, además de peinados de fantasía de Sergio Estudio y tres vestidos del Taller de Costura del Programa de la Mujer municipal.

La directora de DIDECO de Angol, Katia Guzmán, destacó que el evento mostró moda sustentable, talento juvenil y el trabajo del taller municipal. Al cierre se reconoció a Cecilia Muñoz del Departamento de Turismo por su organización.
TXT,
    ],
    [
        'title' => 'Dos lesionados deja colisión frente a Sodimac en Angol',
        'slug' => 'colision-sodimac-avenida-ohiggins-angol',
        'excerpt' => 'Un Toyota gris impactó a otro vehículo en Avenida O’Higgins; la pareja fue trasladada al hospital local.',
        'published_at' => '2026-05-10 11:30:00',
        'source_name' => 'Malleco7',
        'source_url' => 'https://www.malleco7.cl/dos-lesionados-deja-colision-frente-a-sodimac-en-angol/',
        'body' => <<<'TXT'
Pasadas las 11:00 horas, dos automóviles chocaron en Avenida O’Higgins, frente al acceso principal de Sodimac en Angol.

Según testigos, un Toyota gris patente FDBF-57, conducido por un adulto mayor que iba de oriente a poniente, viró a la izquierda e impactó a otro Toyota rojo patente NP-1276, en el que viajaba una pareja en sentido contrario hacia Huequén. El vehículo rojo terminó contra la base de un poste de alumbrado.

Bomberos de Rescate Vehicular, Carabineros y ambulancias del Samu concurrieron al lugar. La pareja fue trasladada al Hospital de Angol. El conductor del primer móvil fue llevado al recinto asistencial para constatación de lesiones y alcoholemia de rigor.
TXT,
    ],
    [
        'title' => 'Niños del Programa 4 a 7 visitaron el Aeródromo Los Confines en Angol',
        'slug' => 'ninos-programa-4-a-7-aerodromo-los-confines-angol',
        'excerpt' => 'Estudiantes de entre 6 y 13 años de tres colegios de Angol conocieron el funcionamiento del recinto aeronáutico.',
        'published_at' => '2026-06-10 15:00:00',
        'source_name' => 'Malleco7',
        'source_url' => 'https://www.malleco7.cl/ninos-de-tres-colegios-de-angol-visitaron-aerodromo-los-confines/',
        'body' => <<<'TXT'
En el marco de la Estrategia Comunal de Intervención Urbana, el Programa 4 a 7 de Angol organizó visitas al Aeródromo Los Confines para niños y niñas de entre 6 y 13 años de la Escuela José Elías Bolívar, el Colegio Aragón y la Escuela Hermanos Carrera.

Los estudiantes recorrieron las instalaciones, conocieron las labores del Club Aéreo y el rol del aeródromo para la comuna y la provincia de Malleco. Para muchos fue la primera vez que accedían al recinto.

La monitora Ximena Bernales y la coordinadora Camila Orellana agradecieron al administrador Roberto Muñoz por la visita guiada. La actividad busca fortalecer el aprendizaje y el desarrollo integral de los participantes del programa municipal.
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

echo $apply ? "=== APLICAR lote 10 noticias ANGOL ===\n\n" : "=== DRY-RUN lote 10 noticias ANGOL ===\n\n";

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
            'comuna' => 'Angol',
            'provincia' => 'Malleco',
            'categoria' => 'angol',
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
    echo "Listo: {$created} creadas, {$updated} actualizadas (comuna: Angol).\n";
} else {
    echo "Dry-run OK. Usa --apply para publicar.\n";
}
