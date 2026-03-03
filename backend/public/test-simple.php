<?php
// Test simple endpoint sin Laravel
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
    
    echo json_encode([
        'success' => true,
        'message' => 'Test endpoint working',
        'content_length' => strlen($content),
        'source' => $source,
        'detected_articles' => substr_count($content, '🚨')
    ]);
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
