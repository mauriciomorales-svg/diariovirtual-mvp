<?php

// Test simple endpoint sin middleware
Route::post('/test-batch', function (Illuminate\Http\Request $request) {
    $content = $request->input('batch_content');
    $source = $request->input('source_name', 'Test');
    
    return response()->json([
        'success' => true,
        'message' => 'Test endpoint working',
        'content_length' => strlen($content),
        'source' => $source
    ]);
});
