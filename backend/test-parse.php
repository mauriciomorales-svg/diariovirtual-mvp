<?php

// Test para verificar el parseo
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$content = "🚨 Test de servidor activo
URL: https://server-test.com
Contenido: Verificando que el servidor Laravel está funcionando correctamente
Fuente: Server Test";

$lines = preg_split('/\r\n|\r|\n/', $content);
$articles = [];
$currentArticle = [];

foreach ($lines as $line) {
    $line = trim($line);
    echo "Processing line: " . $line . "\n";
    
    // Fix emoji detection
    if (str_starts_with($line, '🚨') || str_starts_with($line, '??') || strpos($line, '🚨') === 0) {
        if (!empty($currentArticle) && isset($currentArticle['title'])) {
            $articles[] = $currentArticle;
            echo "Added article: " . $currentArticle['title'] . "\n";
        }
        $currentArticle = [
            'title' => trim(str_replace(['🚨', '??'], '', $line)),
            'url' => '',
            'content' => '',
            'source' => 'Test'
        ];
        echo "New article title: " . $currentArticle['title'] . "\n";
    } elseif (str_starts_with($line, 'URL:')) {
        $currentArticle['url'] = trim(str_replace('URL:', '', $line));
    } elseif (str_starts_with($line, 'Contenido:')) {
        $currentArticle['content'] = trim(str_replace('Contenido:', '', $line));
    } elseif (str_starts_with($line, 'Fuente:')) {
        $currentArticle['source'] = trim(str_replace('Fuente:', '', $line));
    }
}

if (!empty($currentArticle) && isset($currentArticle['title'])) {
    $articles[] = $currentArticle;
    echo "Added final article: " . $currentArticle['title'] . "\n";
}

echo "Total articles parsed: " . count($articles) . "\n";

echo json_encode([
    'success' => true,
    'articles_detected' => count($articles),
    'articles' => $articles
], JSON_PRETTY_PRINT);
?>
