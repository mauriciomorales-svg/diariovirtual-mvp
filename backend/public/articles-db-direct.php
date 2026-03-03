<?php
header('Content-Type: application/json');

// Direct database connection without Laravel bootstrap
try {
    $host = 'localhost';
    $dbname = 'diariovirtual';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $page = $_GET['page'] ?? 1;
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    
    // Get real articles from database
    $stmt = $pdo->prepare("
        SELECT title, slug, excerpt, content, image_url, published_at, is_external, external_url 
        FROM articles 
        WHERE published = 1 
        ORDER BY published_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE published = 1");
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    
    $response = [
        'data' => $articles,
        'current_page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'last_page' => ceil($total / $perPage),
        'showing' => "Showing " . ($offset + 1) . " to " . min($offset + $perPage, $total) . " of $total articles",
        'source' => 'database_direct_connection'
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'message' => 'Failed to connect to database directly',
        'trace' => $e->getTraceAsString()
    ]);
}
?>
