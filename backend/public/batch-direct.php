<?php

// Endpoint simplificado que guarda directamente en BD
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
        
        // Conectar directamente a la base de datos usando config de Laravel
        require_once __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel');
        
        $pdo = new PDO(
            'mysql:host=localhost;dbname=diariovirtual;charset=utf8mb4', 
            'root', 
            '', 
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        
        $processedCount = 0;
        foreach ($articles as $article) {
            // Transformar simple (simular Gemini)
            $transformedTitle = '🚨 ' . $article['title'];
            $transformedContent = $article['content'] . "\n\n[NATIVE_AD_PLACEHOLDER]\n\nContenido localizado para Malleco.";
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $article['title'])));
            $sourceHash = hash('sha256', $article['url'] ?: $article['title']);
            
            // Insertar en base de datos
            $stmt = $pdo->prepare("
                INSERT INTO articles (
                    id, title, slug, source_hash, excerpt, content, 
                    image_url, is_external, external_url, status, 
                    published_at, created_at, updated_at, metadata
                ) VALUES (
                    UUID(), ?, ?, ?, ?, ?, ?, 1, ?, 'published', 
                    NOW(), NOW(), NOW(), ?
                )
            ");
            
            $stmt->execute([
                $transformedTitle,
                $slug,
                $sourceHash,
                substr($article['content'], 0, 255),
                $transformedContent,
                'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco',
                $article['url'] ?: '',
                json_encode([
                    'original_source' => $article['source'],
                    'local_focus' => 'malleco',
                    'word_count' => str_word_count($transformedContent),
                    'processed_by' => 'batch_import_direct'
                ])
            ]);
            
            $processedCount++;
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
