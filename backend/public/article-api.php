<?php
header('Content-Type: application/json');

$slug = $_GET['slug'] ?? 'test-article-1';

// Simple response for single article
echo json_encode([
    'title' => 'Test Article 1 - 🚨 Breaking News',
    'slug' => $slug,
    'excerpt' => 'This is a test article to verify API functionality',
    'content' => 'Test content paragraph 1.\n\nTest content paragraph 2 with [NATIVE_AD_PLACEHOLDER].\n\nTest content paragraph 3.',
    'image_url' => 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco',
    'published_at' => '2026-03-01T16:50:00Z',
    'is_external' => false,
    'external_url' => null
]);
?>
