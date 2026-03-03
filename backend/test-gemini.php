<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\GeminiService;

echo "🔍 Testing Gemini AI Service...\n";

try {
    $gemini = new GeminiService();
    echo "✅ GeminiService created\n";
    
    $health = $gemini->healthCheck();
    echo "📊 Health Check Results:\n";
    print_r($health);
    
    if ($health['available']) {
        echo "\n✅ Gemini AI is WORKING!\n";
        echo "Model: " . $health['model'] . "\n";
    } else {
        echo "\n❌ Gemini AI is NOT working\n";
        echo "Error: " . $health['error'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🔍 Testing complete.\n";
?>
