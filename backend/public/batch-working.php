<?php

// Endpoint final que guarda directamente en BD sin Laravel
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    error_log("Raw input: " . $rawInput);
    
    // Fix UTF-8 encoding before JSON decode
    $rawInput = mb_convert_encoding($rawInput, 'UTF-8', 'UTF-8');
    
    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        echo json_encode(['success' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()]);
        exit;
    }
    
    error_log("Decoded input: " . print_r($input, true));
    
    $content = $input['batch_content'] ?? '';
    $source = $input['source_name'] ?? 'Chat AI Batch';
    
    error_log("Content before encoding: " . $content);
    // Fix encoding
    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
    error_log("Input content: " . $content);
    
    try {
        // Parsear noticias
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $articles = [];
        $currentArticle = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            error_log("Processing line: " . $line);
            
            // Fix emoji detection - handle multiple encodings
            if (str_starts_with($line, '🚨') || str_starts_with($line, '??') || strpos($line, '🚨') === 0 || strpos($line, '??') === 0) {
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
        }
        
        // Usar la conexión de Laravel
        require_once __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        
        // Inicializar la aplicación Laravel
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        
        // Usar la conexión de Laravel
        $db = $app->make('db');
        $pdo = $db->connection()->getPdo();
        
        $processedCount = 0;
        foreach ($articles as $article) {
            // Transformar simple (simular Gemini)
            $transformedTitle = '🚨 ' . $article['title'];
            $transformedContent = $article['content'] . "\n\n[NATIVE_AD_PLACEHOLDER]\n\nContenido localizado para Malleco y la provincia.";
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $article['title'])));
            $sourceHash = hash('sha256', $article['url'] ?: $article['title']);
            
            // Generar UUID en PHP
            $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            
            // Insertar en base de datos - handle duplicates
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO articles (
                        id, title, slug, source_hash, excerpt, content, 
                        image_url, is_external, external_url, status, 
                        published_at, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, 1, ?, 'published', 
                        CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                    )
                ");
                
                $stmt->execute([
                    $uuid,
                    $transformedTitle,
                    $slug,
                    $sourceHash,
                    substr($article['content'], 0, 255),
                    $transformedContent,
                    'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco',
                    $article['url'] ?: ''
                ]);
                
                $processedCount++;
                
            } catch (PDOException $e) {
                // Check if it's a duplicate error
                if (str_contains($e->getMessage(), 'UNIQUE constraint failed: articles.source_hash')) {
                    // Update existing article
                    $stmt = $pdo->prepare("
                        UPDATE articles SET 
                            title = ?, content = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE source_hash = ?
                    ");
                    
                    $stmt->execute([
                        $transformedTitle,
                        $transformedContent,
                        $sourceHash
                    ]);
                    
                    $processedCount++;
                } else {
                    throw $e;
                }
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
            'message' => "Se guardaron {$processedCount} noticias en la base de datos",
            'articles_detected' => count($articles),
            'articles_processed' => $processedCount,
            'preview' => $preview,
            'database' => 'direct_insert_success'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
