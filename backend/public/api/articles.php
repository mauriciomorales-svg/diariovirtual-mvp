<?php
header('Content-Type: application/json');

// Simular respuesta de artículos
$articles = [
    [
        'id' => '1',
        'title' => '🚨 Llaman a vacunarse oportunamente contra la Influenza y el Covid-19',
        'slug' => 'llaman-a-vacunarse-oportunamente-contra-la-influenza-y-el-covid-19',
        'excerpt' => 'Mañana domingo 1 de marzo parte oficialmente la campaña de vacunación.',
        'image_url' => 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Virtual',
        'is_external' => true,
        'external_url' => 'https://www.malleco7.cl/llaman-a-vacunarse-oportunamente-contra-la-influenza-y-el-covid-19/',
        'status' => 'published',
        'published_at' => '2026-03-01T04:06:28.000000Z'
    ],
    [
        'id' => '2',
        'title' => '🚨 Hospital de Curacautín inaugura primera etapa de su nueva y moderna infraestructura',
        'slug' => 'hospital-de-curacautin-inaugura-primera-etapa-de-su-nueva-y-moderna-infraestructura',
        'excerpt' => 'La obra contempla 8.870 metros cuadrados construidos.',
        'image_url' => 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Virtual',
        'is_external' => true,
        'external_url' => 'https://www.malleco7.cl/hospital-de-curacautin-inaugura-primera-etapa-de-su-nueva-y-moderna-infraestructura/',
        'status' => 'published',
        'published_at' => '2026-03-01T04:06:28.000000Z'
    ]
];

echo json_encode(['data' => $articles]);
?>
