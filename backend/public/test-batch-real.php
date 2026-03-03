<?php

// Test simple endpoint para debug
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $content = $input['batch_content'] ?? '';
    $source = $input['source_name'] ?? 'Test';
    
    // Simular procesamiento real
    $lines = explode("\n", $content);
    $articles = [];
    $currentArticle = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        if (str_starts_with($line, '🚨')) {
            if (!empty($currentArticle)) {
                $articles[] = $currentArticle;
            }
            $currentArticle = [
                'title' => trim(str_replace('🚨', '', $line)),
                'url' => '',
                'content' => '',
                'source' => $source
            ];
        } elseif (str_starts_with($line, 'URL:')) {
            $currentArticle['url'] = trim(str_replace('URL:', '', $line));
        } elseif (str_starts_with($line, 'Contenido:')) {
            $currentArticle['content'] = trim(str_replace('Contenido:', '', $line));
        } elseif (str_starts_with($line, 'Fuente:')) {
            $currentArticle['source'] = trim(str_replace('Fuente:', '', $line));
        }
    }
    
    if (!empty($currentArticle)) {
        $articles[] = $currentArticle;
    }
    
    // Crear preview
    $preview = array_map(function($article) {
        return [
            'title' => $article['title'],
            'source' => $article['source'],
            'content_length' => strlen($article['content']),
            'has_url' => !empty($article['url'])
        ];
    }, $articles);
    
    echo json_encode([
        'success' => true,
        'message' => "Se enviaron " . count($articles) . " noticias a procesamiento",
        'articles_detected' => count($articles),
        'articles_processed' => count($articles),
        'preview' => $preview
    ]);
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
