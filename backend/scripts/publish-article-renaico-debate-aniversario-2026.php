<?php
/**
 * Información — Renaico aniversario $290M (dato verificado 3908-28-LP25).
 *
 *   php scripts/publish-article-renaico-debate-aniversario-2026.php
 *   php scripts/publish-article-renaico-debate-aniversario-2026.php --apply
 */
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Str;

$apply = in_array('--apply', $argv, true);

$licitacionUrl = 'https://www.mercadopublico.cl/Procurement/Modules/RFB/DetailsAcquisition.aspx?qs=TyOoXxPK2QO/NF7JnUQnfQ==';
$terramUrl = 'https://www.terram.cl/fuera-de-norma-17-municipios-aprobaron-proyectos-ambientales-de-empresas-que-les-realizaron-millonarias-donaciones/';
$sourceHash = hash('sha256', 'diario-zona-sur-opinion-renaico-debate-aniversario-2026-3908-28-LP25');

$title = 'Angol se aprovecha de Renaico';
$slug = 'renaico-escenario-artistico-angol-debate-aniversario-290-millones';

$excerpt = 'Renaico licitó $290 millones por cuatro noches de aniversario (3908-28-LP25). Sin desglose público. Terram documenta más de $1.500 millones en donaciones eólicas (2018–2024).';

$imgHero = 'https://api.diariozonasur.cl/images/renaico-dinero-se-queda-se-va-estimacion.jpg?v=4';

$content = <<<HTML
<p>El título apunta al debate sobre cómo se invierten los recursos comunales en Renaico, no a culpar a quien asiste al show. Los vecinos de Angol —y toda la región— son siempre bienvenidos. El fondo es otro: transparencia, cuánto se licita y cuánto queda en la comuna.</p>

<p>Cada enero, la Municipalidad de Renaico licita en Mercado Público la producción de su aniversario comunal: cuatro noches, entrada libre, escenario desmontado el domingo. En 2026 el contrato adjudicado alcanza <strong>\$290.000.000</strong>, IVA incluido.</p>

<p>El municipio no publica desglose entre artistas, productora, técnica externa ni proveedores locales. Tampoco hay registro público de cuánto de ese gasto vuelve al comercio de la comuna.</p>

<h2>El contrato de \$290 millones</h2>

<p>Publicada el 18 de diciembre de 2025, adjudicada el 22 de enero de 2026.</p>

<blockquote>
<p><strong>\$290.000.000 — IVA incluido</strong><br>
Licitación <strong>3908-28-LP25</strong> · 4 días<br>
<em>Producción de eventos y servicio técnico (audio, iluminación, pantallas, escenario, vallas), Aniversario Comunal Renaico 2026.</em><br>
<a href="{$licitacionUrl}" target="_blank" rel="noopener noreferrer">Ver ficha en Mercado Público</a></p>
</blockquote>

<p>Puede haber otros gastos del aniversario por otras vías; no figuran en los documentos consultados.</p>

<h2>¿Cuánto se queda en Renaico?</h2>

<p>El municipio no publica desglose entre artistas, productora, técnica externa ni proveedores locales. El gráfico de esta nota —<strong>estimación ilustrativa</strong>, no auditoría— sugiere que la mayor parte del contrato sale de la comuna; una fracción menor podría quedar en comercio y empleos del evento.</p>

<table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:640px;">
<tr style="background:#f1f5f9;"><th>Concepto</th><th>Monto estimado</th><th>Referencia</th></tr>
<tr><td><strong>Sale de Renaico</strong></td><td>~\$255 millones (~88%)</td><td>Productora, cartel, técnica externa</td></tr>
<tr><td><strong>Podría quedar</strong></td><td>~\$35 millones (~12%)</td><td>Comercio local, empleos puntuales</td></tr>
</table>

<h2>Donaciones eólicas — transparencia municipal</h2>

<p>Según la investigación <strong>«Fuera de norma»</strong> de <em>The Clinic</em>, publicada por <a href="{$terramUrl}" target="_blank" rel="noopener noreferrer">Fundación Terram</a> (5 de enero de 2025), Renaico encabeza el ranking nacional de donaciones ligadas a evaluaciones ambientales.</p>

<p>Desde <strong>2018</strong>, la comuna recibió donaciones de privados por <strong>más de \$1.500 millones</strong>, en su mayoría de empresas generadoras de energía. Entre <strong>2018 y 2024</strong> el cruce con el SEA arroja <strong>\$1.214.862.089</strong> de empresas cuyos proyectos fueron aprobados por el mismo municipio y <strong>\$371 millones</strong> de empresas cuyos proyectos recibieron observaciones de Renaico.</p>

<p>Entre <strong>2018 y 2023</strong>, más de <strong>\$1.500 millones</strong> llegaron de Consorcio Eólico San Gabriel SpA, Enel Chile S.A., Enel Green Power del Sur, Parque Eólico San Gabriel SpA, Tolpán Sur SpA y Vientos de Renaico SpA. Un episodio documentado: el <strong>7 de noviembre de 2018</strong> Vientos de Renaico SpA donó <strong>\$22,8 millones</strong>; <strong>trece días después</strong> el municipio se pronunció conforme al Parque Eólico Vergara.</p>

<p>Son <strong>donaciones voluntarias</strong> en transparencia municipal, no un canon legal de los parques en operación. Terram no detalla en qué se gastó ese dinero en Renaico.</p>

<h2>Gasto e inversión</h2>

<p>Cuatro noches de licitación, montaje y desmontaje: <strong>gasto</strong> que se consume y termina. <strong>Inversión</strong> —equipos municipales reutilizables, contratos locales, río, ferrocarril, balneario— permanece cuando se apagan las luces.</p>

<h2>Lo que hicieron otras comunas: equipos propios y productores locales</h2>

<p>Varias municipalidades dejaron de arrendar técnica evento a evento y <strong>compraron equipamiento</strong>, contratando personal local para operarlo. Los precios de compra y arriendo provienen de licitaciones públicas — no de Renaico, sino de comunas que ya publicaron desgloses en Mercado Público.</p>

<p><strong>Los Andes — arriendo por evento (2026):</strong> carnaval andino: sonido <strong>\$12 millones</strong>, iluminación <strong>\$10 millones</strong>, pantallas LED <strong>\$8 millones</strong> — <strong>\$30 millones</strong> solo técnica, una jornada (<a href="https://www.todolicitaciones.cl/licitacion/2799-6-LE26/arriendo-de-sistema-audiovisual-para-carnaval-andino-2026" target="_blank" rel="noopener noreferrer">2799-6-LE26</a>). Es lo que hoy se paga por arrendar lo que Renaico licita embebido en la 3908-28-LP25.</p>

<p><strong>Coquimbo — compra municipal:</strong> invirtió <strong>\$153 millones</strong> en audio, iluminación LED, pantalla 6×4 m y estructuras; estimó ahorrar <strong>\$120 millones anuales</strong> en arriendos y recuperar la inversión en ~20 meses operando ~4 eventos al mes con equipo propio (<a href="https://www.elobservatodo.cl/noticia/cultura/con-compra-de-equipos-para-eventos-municipalidad-de-coquimbo-busca-ahorrar-costos-en" target="_blank" rel="noopener noreferrer">El Observatodo</a>).</p>

<div style="overflow-x:auto;margin:2rem 0;border-radius:14px;box-shadow:0 12px 40px rgba(15,23,42,0.12);border:1px solid #e2e8f0;background:#ffffff;">
<table style="width:100%;min-width:560px;border-collapse:collapse;font-size:14px;line-height:1.5;display:table;">
<thead>
<tr>
<th style="background:linear-gradient(135deg,#0f766e 0%,#115e59 100%);color:#fff;padding:16px 20px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">Equipo (compra — ref. comunas)</th>
<th style="background:linear-gradient(135deg,#0f766e 0%,#115e59 100%);color:#fff;padding:16px 20px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">Precio referencial</th>
<th style="background:linear-gradient(135deg,#0f766e 0%,#115e59 100%);color:#fff;padding:16px 20px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">Arriendo (ref. Los Andes / 4 noches est.)</th>
</tr>
</thead>
<tbody>
<tr><td style="padding:14px 20px;background:#f0fdfa;font-weight:600;color:#0f172a;border-bottom:1px solid #ccfbf1;">Sistema de sonido PA (5.000+ personas)</td><td style="padding:14px 20px;border-bottom:1px solid #ccfbf1;">~\$48 millones</td><td style="padding:14px 20px;color:#64748b;border-bottom:1px solid #ccfbf1;">~\$14 millones <em>(ref. Los Andes \$12M, 1 noche)</em></td></tr>
<tr><td style="padding:14px 20px;background:#fff;font-weight:600;color:#0f172a;border-bottom:1px solid #ccfbf1;">Iluminación escenario LED</td><td style="padding:14px 20px;border-bottom:1px solid #ccfbf1;">~\$38 millones</td><td style="padding:14px 20px;color:#64748b;border-bottom:1px solid #ccfbf1;">~\$12 millones <em>(ref. \$10M, 1 noche)</em></td></tr>
<tr><td style="padding:14px 20px;background:#f0fdfa;font-weight:600;color:#0f172a;border-bottom:1px solid #ccfbf1;">Pantalla LED outdoor ~6×4 m</td><td style="padding:14px 20px;border-bottom:1px solid #ccfbf1;">~\$28 millones</td><td style="padding:14px 20px;color:#64748b;border-bottom:1px solid #ccfbf1;">~\$10 millones <em>(ref. \$8M, 1 noche)</em></td></tr>
<tr><td style="padding:14px 20px;background:#fff;font-weight:600;color:#0f172a;border-bottom:1px solid #ccfbf1;">Escenario modular + tarimas</td><td style="padding:14px 20px;border-bottom:1px solid #ccfbf1;">~\$22 millones</td><td style="padding:14px 20px;color:#64748b;border-bottom:1px solid #ccfbf1;">~\$8 millones</td></tr>
<tr><td style="padding:14px 20px;background:#f0fdfa;font-weight:600;color:#0f172a;border-bottom:1px solid #ccfbf1;">Generador + distribución eléctrica</td><td style="padding:14px 20px;border-bottom:1px solid #ccfbf1;">~\$14 millones</td><td style="padding:14px 20px;color:#64748b;border-bottom:1px solid #ccfbf1;">~\$5 millones</td></tr>
<tr><td style="padding:14px 20px;background:#fff;font-weight:600;color:#0f172a;border-bottom:1px solid #ccfbf1;">Vallas, CCTV, accesorios</td><td style="padding:14px 20px;border-bottom:1px solid #ccfbf1;">~\$10 millones</td><td style="padding:14px 20px;color:#64748b;border-bottom:1px solid #ccfbf1;">~\$4 millones</td></tr>
<tr style="background:#0f172a;color:#fff;"><td style="padding:16px 20px;font-weight:700;">Total equipamiento</td><td style="padding:16px 20px;font-weight:700;">~\$160 millones</td><td style="padding:16px 20px;font-weight:700;">~\$53 millones <em>(solo arriendo técnico, 4 noches)</em></td></tr>
</tbody>
</table>
</div>

<p><strong>Equipo humano local — como en Coquimbo:</strong> 2 técnicos de sonido/luz (<strong>~\$13 millones brutos c/u al año</strong>) + 1 coordinador de eventos (~\$15 millones/año) + capacitación inicial (~\$6 millones, una vez) + mantención (~\$8 millones/año). <strong>~\$49 millones/año</strong> en sueldos y operación recurrente (~\$55 millones el primer año, incluyendo capacitación) vs. licitar productora externa cada vez. <em>Referencia salarial estimada; Coquimbo no publicó desglose de personal en la fuente consultada.</em></p>

<p><strong>Inversión inicial (referencia comunas):</strong> ~\$160 millones (equipos, calibrado con Coquimbo \$153M) + ~\$6 millones (capacitación) = <strong>~\$166 millones, una sola vez</strong>. Coquimbo demostró que la técnica deja de arrendarse; el cartel y la producción pueden quedar en manos locales si el municipio forma productores propios.</p>

<p><strong>¿Qué obtuvieron los habitantes donde se hizo?</strong></p>
<ul>
<li><strong>Empleo local permanente</strong> — técnicos y coordinadores de la comuna, no solo montaje externo en enero.</li>
<li><strong>Más eventos sin licitar productora</strong> — Coquimbo calculó ~4 actividades mensuales con los mismos equipos; en Renaico podrían reutilizarse en Muestra Costumbrista, Día del río, triatlón o el Recinto Estación.</li>
<li><strong>Ahorro técnico recurrente</strong> — arrendar 4 noches equivale a ~\$53 millones (estimación a partir de Los Andes); Coquimbo recuperó \$153 millones en ~20 meses con uso frecuente.</li>
<li><strong>Capacitación juvenil</strong> — sonido e iluminación como oficio local, como impulsó Coquimbo al internalizar la operación.</li>
<li><strong>Nuevos productores de eventos locales</strong> — tener equipos y equipo técnico propio <strong>obliga</strong> a formar productores renaiquinos que manejen cartel, logística y montaje durante el año, en lugar de depender cada enero de una licitación integral a productora externa.</li>
</ul>

<p><em>Referencia comparativa: ~\$166 millones una vez (equipos + capacitación, como Coquimbo) frente a \$290 millones cada enero (licitación integral verificada de Renaico). Son decisiones distintas — gasto recurrente vs. activo municipal — documentadas en otras comunas, no en el presupuesto actual de Renaico.</em></p>

<h2>Comunas exitosas vs Renaico</h2>

<p>Referencia: buenas prácticas de gestión de destinos (UN Tourism, G20 Goa Roadmap). Columna Renaico: solo lo documentado en fuentes públicas consultadas.</p>

<div style="overflow-x:auto;margin:2rem 0;border-radius:14px;box-shadow:0 12px 40px rgba(15,23,42,0.12);border:1px solid #e2e8f0;background:#ffffff;">
<table style="width:100%;min-width:520px;border-collapse:collapse;font-size:15px;line-height:1.55;display:table;">
<thead>
<tr>
<th style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);color:#ffffff;padding:18px 22px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;border-bottom:3px solid #b91c1c;width:44%;">Comunas exitosas</th>
<th style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);color:#ffffff;padding:18px 22px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;border-bottom:3px solid #b91c1c;">Renaico · documentado</th>
</tr>
</thead>
<tbody>
<tr><td style="padding:16px 22px;background:#f8fafc;font-weight:600;color:#0f172a;border-bottom:1px solid #e8edf3;vertical-align:top;">Estrategia turística de 12 meses, publicada</td><td style="padding:16px 22px;color:#64748b;border-bottom:1px solid #e8edf3;vertical-align:top;">No hay calendario turístico municipal unificado en documentos consultados</td></tr>
<tr><td style="padding:16px 22px;background:#ffffff;font-weight:600;color:#0f172a;border-bottom:1px solid #e8edf3;vertical-align:top;">Medir visitantes, gasto local e impacto económico</td><td style="padding:16px 22px;color:#64748b;border-bottom:1px solid #e8edf3;vertical-align:top;">No hay censo publicado de asistentes ni desglose de la licitación \$290M</td></tr>
<tr><td style="padding:16px 22px;background:#f8fafc;font-weight:600;color:#0f172a;border-bottom:1px solid #e8edf3;vertical-align:top;">Retener dinero en la cadena local (PYME, artesanía, empleo)</td><td style="padding:16px 22px;color:#64748b;border-bottom:1px solid #e8edf3;vertical-align:top;">Licitación global a productora externa; estimación editorial ~88% sale de la comuna</td></tr>
<tr><td style="padding:16px 22px;background:#ffffff;font-weight:600;color:#0f172a;border-bottom:1px solid #e8edf3;vertical-align:top;">Diversificar: naturaleza, cultura, deporte, patrimonio</td><td style="padding:16px 22px;color:#64748b;border-bottom:1px solid #e8edf3;vertical-align:top;">Existe río, balneario, Muestra Costumbrista y triatlón, pero el gasto visible se concentra en el show de 4 noches</td></tr>
<tr><td style="padding:16px 22px;background:#f8fafc;font-weight:600;color:#0f172a;border-bottom:1px solid #e8edf3;vertical-align:top;">Invertir en activos que quedan (rutas, patrimonio, equipamiento)</td><td style="padding:16px 22px;color:#64748b;border-bottom:1px solid #e8edf3;vertical-align:top;">Recinto Estación concentra eventos; estación ferroviaria (1884–1885) sin activación turística publicada equivalente al show</td></tr>
<tr><td style="padding:16px 22px;background:#ffffff;font-weight:600;color:#0f172a;border-bottom:1px solid #e8edf3;vertical-align:top;">Transparencia: desglose de presupuesto cultural</td><td style="padding:16px 22px;color:#64748b;border-bottom:1px solid #e8edf3;vertical-align:top;">Mercado Público consigna total 3908-28-LP25; sin reparto artístico/técnico publicado</td></tr>
<tr><td style="padding:16px 22px;background:#f8fafc;font-weight:600;color:#0f172a;vertical-align:top;">Cuenta pública de aportes externos (donaciones, convenios)</td><td style="padding:16px 22px;color:#64748b;vertical-align:top;">Terram documenta &gt;\$1.500M (2018–2024); sin detalle publicado de en qué se gastó en Renaico</td></tr>
</tbody>
</table>
</div>

<p>El domingo el público se va. Renaico sigue. La pregunta abierta: ¿cuánto de los <strong>\$290 millones</strong> de enero y de los <strong>\$1.500 millones</strong> documentados por Terram se traduce en algo que quede el lunes?</p>

<p><small><em>Fuentes: Mercado Público 3908-28-LP25; Terram/The Clinic, enero 2025; Los Andes 2799-6-LE26; Coquimbo (El Observatodo). Desglose equipos: referencia comunas. Gráfico 88/12: estimación editorial.</em></small></p>
HTML;

$payload = [
    'title' => $title,
    'slug' => $slug,
    'source_hash' => $sourceHash,
    'excerpt' => Str::limit($excerpt, 252),
    'content' => $content,
    'image_url' => $imgHero,
    'is_external' => false,
    'external_url' => null,
    'status' => 'published',
    'published_at' => now(),
    'metadata' => [
        'original_source' => 'Diario Zona Sur',
        'authors' => 'Diario Zona Sur',
        'region' => 'La Araucanía',
        'comuna' => 'Renaico',
        'tipo' => 'informacion',
        'dato_verificado' => '3908-28-LP25',
        'licitacion_url' => $licitacionUrl,
    ],
];

$existing = Article::query()
    ->where('source_hash', $sourceHash)
    ->orWhere('slug', $slug)
    ->first();

$publicUrl = 'https://diariozonasur.cl/'.$slug;
$apiUrl = 'https://api.diariozonasur.cl/api/v1/articles/'.$slug;

echo $apply ? "=== APLICAR publicación ===\n" : "=== DRY-RUN ===\n";
echo "Título: {$title}\n";
echo "Slug: {$slug}\n";
echo "Web: {$publicUrl}\n";
echo "API: {$apiUrl}\n";

if ($existing) {
    echo "\nYa existe artículo #{$existing->id}\n";
    if ($apply) {
        $existing->fill($payload);
        $existing->save();
        echo "Actualizado #{$existing->id}\n";
    }
    exit(0);
}

if (! $apply) {
    echo "\nDry-run OK. Usa --apply para publicar.\n";
    exit(0);
}

$article = Article::create($payload);
echo "\nCreado artículo #{$article->id}\n";
