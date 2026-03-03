<?php
header('Content-Type: application/json');

// Simple response without Laravel bootstrap for testing
echo json_encode([
    'data' => [
        [
            'title' => 'Test Article 1 - 🚨 Breaking News',
            'slug' => 'test-article-1',
            'excerpt' => 'This is a test article to verify API functionality',
            'content' => 'Test content paragraph 1.\n\nTest content paragraph 2 with [NATIVE_AD_PLACEHOLDER].\n\nTest content paragraph 3.',
            'image_url' => 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco',
            'published_at' => '2026-03-01T16:50:00Z',
            'is_external' => false,
            'external_url' => null
        ],
        [
            'title' => 'Test Article 2 - 🚨 Local News',
            'slug' => 'test-article-2',
            'excerpt' => 'Another test article for local news verification',
            'content' => 'Local news content paragraph 1.\n\nLocal news paragraph 2 with [NATIVE_AD_PLACEHOLDER].\n\nLocal news paragraph 3.',
            'image_url' => 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco',
            'published_at' => '2026-03-01T15:45:00Z',
            'is_external' => false,
            'external_url' => null
        ]
    ],
    'current_page' => 1,
    'per_page' => 20,
    'total' => 2,
    'last_page' => 1,
    'showing' => 'Showing 1 to 2 of 2 articles'
]);
?>
