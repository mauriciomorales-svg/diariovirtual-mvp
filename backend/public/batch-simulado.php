<?php

// Endpoint que usa Laravel directamente
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
    $source = $input['source_name'] ?? 'Chat AI Batch';
    
    try {
        // Parsear noticias
        $lines = explode("\n", $content);
        $articles = [];
        $currentArticle = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (str_starts_with($line, '🚨')) {
                if (!empty($currentArticle) && isset($currentArticle['title'])) {
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
        
        if (!empty($currentArticle) && isset($currentArticle['title'])) {
            $articles[] = $currentArticle;
        }
        
        // Simular guardado exitoso (sin BD real por ahora)
        $processedCount = count($articles);
        
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
            'message' => "Se procesaron {$processedCount} noticias (simulado - sin BD)",
            'articles_detected' => count($articles),
            'articles_processed' => $processedCount,
            'preview' => $preview,
            'note' => 'Modo simulación - Para guardar real, necesita conexión Laravel'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
