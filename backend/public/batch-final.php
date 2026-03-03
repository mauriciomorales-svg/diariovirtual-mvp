<?php

// Endpoint final que conecta con Laravel correctamente
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
        // Debug: log input
        error_log("Batch input: " . $content);
        
        // Parsear noticias - normalizar líneas
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $articles = [];
        $currentArticle = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            error_log("Processing line: " . $line);
            
            // Fix emoji detection - handle multiple encodings
            if (str_starts_with($line, '🚨') || str_starts_with($line, '??') || strpos($line, '🚨') === 0) {
                if (!empty($currentArticle) && isset($currentArticle['title'])) {
                    $articles[] = $currentArticle;
                    error_log("Added article: " . $currentArticle['title']);
                }
                $currentArticle = [
                    'title' => trim(str_replace(['🚨', '??'], '', $line)),
                    'url' => '',
                    'content' => '',
                    'source' => $source
                ];
                error_log("New article title: " . $currentArticle['title']);
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
            error_log("Added final article: " . $currentArticle['title']);
        }
        
        error_log("Total articles parsed: " . count($articles));
        
        // Conectar con Laravel usando cURL
        $processedCount = 0;
        foreach ($articles as $article) {
            $data = [
                'original_title' => $article['title'],
                'original_content' => $article['content'],
                'original_url' => $article['url'] ?: 'https://batch-import.local/' . time(),
                'source_name' => $article['source']
            ];
            
            $ch = curl_init('http://127.0.0.1:8000/dev/gemini/process');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 45);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $responseData = json_decode($response, true);
                if ($responseData && isset($responseData['success']) && $responseData['success']) {
                    $processedCount++;
                }
            } else {
                error_log("Batch import error: HTTP $httpCode, Error: $error, Response: $response");
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
            'message' => "Se procesaron {$processedCount} noticias con Gemini AI",
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
