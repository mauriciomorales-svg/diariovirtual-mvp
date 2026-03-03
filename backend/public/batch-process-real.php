<?php

// Endpoint real que conecta con Laravel y Gemini
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
        
        // Conectar con Laravel y enviar a Gemini
        $processedCount = 0;
        foreach ($articles as $article) {
            // Llamar al endpoint real de Laravel
            $laravelData = [
                'original_title' => $article['title'],
                'original_content' => $article['content'],
                'original_url' => $article['url'],
                'source_name' => $article['source']
            ];
            
            $ch = curl_init('http://127.0.0.1:8000/dev/gemini/process');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($laravelData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $processedCount++;
            }
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
            'message' => "Se enviaron {$processedCount} noticias a procesamiento con Gemini AI",
            'articles_detected' => count($articles),
            'articles_processed' => $processedCount,
            'preview' => $preview
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
