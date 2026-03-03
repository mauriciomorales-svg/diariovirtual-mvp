<?php

echo "Testing EMOL XML parsing...\n\n";

$context = stream_context_create([
    'http' => [
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
    ]
]);

$response = file_get_contents('https://www.emol.com/rss/todas.xml', false, $context);

if ($response === false) {
    echo "EMOL: Failed to fetch\n";
    exit;
}

echo "EMOL: Fetched " . strlen($response) . " bytes\n";
echo "First 500 chars:\n";
echo substr($response, 0, 500) . "\n\n";

// Try to parse with SimpleXML
echo "=== Testing SimpleXML ===\n";
try {
    $xml = simplexml_load_string($response);
    if ($xml === false) {
        echo "SimpleXML: Failed to parse\n";
    } else {
        echo "SimpleXML: Success - Found " . count($xml->channel->item) . " items\n";
        echo "Channel title: " . $xml->channel->title . "\n";
    }
} catch (Exception $e) {
    echo "SimpleXML Exception: " . $e->getMessage() . "\n";
}

// Try to parse with DOMDocument
echo "\n=== Testing DOMDocument ===\n";
try {
    $dom = new DOMDocument();
    $dom->loadXML($response);
    echo "DOMDocument: Success\n";
    $items = $dom->getElementsByTagName('item');
    echo "Found " . $items->length . " items\n";
} catch (Exception $e) {
    echo "DOMDocument Exception: " . $e->getMessage() . "\n";
}

// Check for common XML issues
echo "\n=== Checking XML Issues ===\n";
if (strpos($response, '<?xml') === false) {
    echo "❌ Missing XML declaration\n";
} else {
    echo "✅ Has XML declaration\n";
}

if (preg_match('/&[^;]+;/', $response)) {
    echo "❌ Contains unescaped ampersands\n";
} else {
    echo "✅ No unescaped ampersands found\n";
}

if (preg_match('/<[^>]*&[^<]*>/', $response)) {
    echo "❌ Contains HTML entities in tags\n";
} else {
    echo "✅ No HTML entities in tags\n";
}

// Look for encoding issues
if (preg_match('/encoding=["\'][^"\']*["\']/', $response)) {
    echo "✅ Has encoding declaration\n";
} else {
    echo "❌ Missing encoding declaration\n";
}
