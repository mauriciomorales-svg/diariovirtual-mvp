<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use Illuminate\Console\Command;

class TestGemini extends Command
{
    protected $signature = 'gemini:test';
    protected $description = 'Test Gemini AI service functionality';

    public function handle()
    {
        $this->info('🔍 Testing Gemini AI Service...');
        
        try {
            $gemini = new GeminiService();
            $this->info('✅ GeminiService created');
            
            $health = $gemini->healthCheck();
            $this->info('📊 Health Check Results:');
            
            if ($health['available']) {
                $this->info('✅ Gemini AI is WORKING!');
                $this->info('Model: ' . $health['model']);
                $this->info('Status: Available');
            } else {
                $this->error('❌ Gemini AI is NOT working');
                $this->error('Error: ' . $health['error']);
                $this->info('Quota Exceeded: ' . ($health['quota_exceeded'] ? 'Yes' : 'No'));
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
        }
        
        $this->info('🔍 Testing complete.');
        return 0;
    }
}
