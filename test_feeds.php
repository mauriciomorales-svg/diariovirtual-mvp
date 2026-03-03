<?php

echo "Testing RSS Feeds...\n\n";

// Test BioBioChile
echo "=== BioBioChile ===\n";
$context = stream_context_create([
    'http' => [
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
    ]
]);

$response = file_get_contents('https://www.biobiochile.cl/rss/bbcl.xml', false, $context);
if ($response === false) {
    echo "BioBioChile: Failed to fetch\n";
} else {
    echo "BioBioChile: Success - " . strlen($response) . " bytes\n";
    echo "First 200 chars: " . substr($response, 0, 200) . "...\n";
}

echo "\n=== EMOL ===\n";
$response = file_get_contents('https://www.emol.com/rss/todas.xml', false, $context);
if ($response === false) {
    echo "EMOL: Failed to fetch\n";
} else {
    echo "EMOL: Success - " . strlen($response) . " bytes\n";
    echo "First 200 chars: " . substr($response, 0, 200) . "...\n";
}

echo "\n=== Testing alternatives ===\n";

// Test BioBioChile alternatives
$alternatives = [
    'https://www.biobiochile.cl/feed/',
    'https://www.biobiochile.cl/rss/',
    'https://www.biobiochile.cl/noticias/rss/',
    'https://www.biobiochile.cl/region/araucania/rss/',
];

foreach ($alternatives as $url) {
    $response = file_get_contents($url, false, $context);
    if ($response !== false) {
        echo "✓ Alternative works: $url (" . strlen($response) . " bytes)\n";
    } else {
        echo "✗ Alternative failed: $url\n";
    }
}

echo "\n=== Testing local feeds ===\n";
$local_feeds = [
    'https://www.soychile.cl/rss/araucania.xml',
    'https://www.ladiscusion.cl/feed/',
    'https://www.eldiarioladiscusion.cl/feed/',
];

foreach ($local_feeds as $url) {
    $response = file_get_contents($url, false, $context);
    if ($response !== false) {
        echo "✓ Local feed works: $url (" . strlen($response) . " bytes)\n";
    } else {
        echo "✗ Local feed failed: $url\n";
    }
}
