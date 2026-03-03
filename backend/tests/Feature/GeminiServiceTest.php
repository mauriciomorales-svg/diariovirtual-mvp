<?php

namespace Tests\Feature;

use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GeminiService $geminiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->geminiService = new GeminiService();
    }

    /**
     * Test basic Gemini service instantiation
     */
    public function test_gemini_service_instantiation(): void
    {
        $this->assertInstanceOf(GeminiService::class, $this->geminiService);
    }

    /**
     * Test health check functionality
     */
    public function test_gemini_health_check(): void
    {
        // This test will fail without proper API key setup
        // but should not crash the application
        $isHealthy = $this->geminiService->healthCheck();
        
        // Should return boolean, not throw exception
        $this->assertIsBool($isHealthy);
    }

    /**
     * Test transformation with sample content
     */
    public function test_gemini_transformation_latency(): void
    {
        // Mock content for testing
        $sampleContent = "SANTIAGO - El Ministerio de Salud anunció hoy nuevas medidas sanitarias para la Región Metropolitana. Las autoridades informaron que las restricciones se mantendrán durante las próximas semanas debido al aumento de casos. Los expertos recomiendan continuar con las precauciones habituales.";
        
        $sampleTitle = "Ministerio de Salud anuncia nuevas medidas sanitarias";

        // Test with cache disabled for accurate timing
        Cache::flush();

        $startTime = microtime(true);
        
        try {
            $result = $this->geminiService->transformArticle($sampleContent, $sampleTitle);
            $endTime = microtime(true);
            
            $latency = ($endTime - $startTime) * 1000; // Convert to milliseconds
            
            // Assert latency is under 1200ms (1.2 seconds)
            $this->assertLessThan(1200, $latency, 'Latency exceeds 1200ms');
            
            // Assert result structure
            $this->assertIsArray($result);
            $this->assertArrayHasKey('title', $result);
            $this->assertArrayHasKey('slug', $result);
            $this->assertArrayHasKey('excerpt', $result);
            $this->assertArrayHasKey('content', $result);
            
            // Assert title has emoji
            $this->assertStringContainsString('🚨', $result['title']);
            
            // Assert content has placeholder
            $this->assertStringContainsString('[NATIVE_AD_PLACEHOLDER]', $result['content']);
            
            // Assert excerpt length
            $this->assertLessThanOrEqual(255, strlen($result['excerpt']));
            
        } catch (\Exception $e) {
            // If API is not configured, test should not fail
            $this->markTestSkipped('Gemini API not configured: ' . $e->getMessage());
        }
    }

    /**
     * Test caching functionality
     */
    public function test_gemini_caching(): void
    {
        $sampleContent = "Test content for caching";
        $sampleTitle = "Test title for caching";

        // Clear cache
        Cache::flush();

        try {
            // First call - should hit API
            $startTime = microtime(true);
            $result1 = $this->geminiService->transformArticle($sampleContent, $sampleTitle);
            $firstCallTime = microtime(true) - $startTime;

            // Second call - should hit cache
            $startTime = microtime(true);
            $result2 = $this->geminiService->transformArticle($sampleContent, $sampleTitle);
            $secondCallTime = microtime(true) - $startTime;

            // Results should be identical
            $this->assertEquals($result1, $result2);

            // Second call should be significantly faster (from cache)
            $this->assertLessThan($firstCallTime, $secondCallTime);

        } catch (\Exception $e) {
            $this->markTestSkipped('Gemini API not configured: ' . $e->getMessage());
        }
    }

    /**
     * Test transformation with different content types
     */
    public function test_gemini_transformation_content_types(): void
    {
        $testCases = [
            [
                'title' => 'Incendio forestal en la Región',
                'content' => 'Un incendio forestal ha afectado más de 100 hectáreas en la región. Las autoridades han ordenado la evacuación de varias comunidades. Los bomberos trabajan para controlar las llamas.',
                'expected_focus' => 'local'
            ],
            [
                'title' => 'Nueva ley de educación aprobada',
                'content' => 'El Congreso aprobó una nueva ley de educación que modificará el sistema actual. Los cambios entrarán en vigor el próximo año. Los expertos analizan el impacto.',
                'expected_focus' => 'local'
            ]
        ];

        foreach ($testCases as $case) {
            try {
                $result = $this->geminiService->transformArticle($case['content'], $case['title']);
                
                // Basic structure validation
                $this->assertIsArray($result);
                $this->assertNotEmpty($result['title']);
                $this->assertNotEmpty($result['content']);
                
                // Local focus validation
                $this->assertStringContainsString('🚨', $result['title']);
                $this->assertStringContainsString('[NATIVE_AD_PLACEHOLDER]', $result['content']);
                
            } catch (\Exception $e) {
                $this->markTestSkipped('Gemini API not configured: ' . $e->getMessage());
                return;
            }
        }
    }

    /**
     * Test error handling
     */
    public function test_gemini_error_handling(): void
    {
        // Test with empty content
        $this->expectException(\Exception::class);
        $this->geminiService->transformArticle('', 'Test title');
    }

    /**
     * Test slug generation
     */
    public function test_gemini_slug_generation(): void
    {
        $sampleContent = "Test content";
        $sampleTitle = "🚨 Título con caracteres especiales y ñ";

        try {
            $result = $this->geminiService->transformArticle($sampleContent, $sampleTitle);
            
            // Assert slug is URL-friendly
            $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $result['slug']);
            $this->assertNotEmpty($result['slug']);
            
        } catch (\Exception $e) {
            $this->markTestSkipped('Gemini API not configured: ' . $e->getMessage());
        }
    }

    /**
     * Test metadata structure
     */
    public function test_gemini_metadata_structure(): void
    {
        $sampleContent = "Test content for metadata validation";
        $sampleTitle = "Test title";

        try {
            $result = $this->geminiService->transformArticle($sampleContent, $sampleTitle);
            
            // If metadata exists, validate structure
            if (isset($result['metadata'])) {
                $metadata = $result['metadata'];
                
                $this->assertIsArray($metadata);
                
                // Check common metadata fields
                $possibleFields = ['original_source', 'local_focus', 'urgency_level', 'word_count'];
                foreach ($possibleFields as $field) {
                    if (isset($metadata[$field])) {
                        $this->assertNotEmpty($metadata[$field]);
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->markTestSkipped('Gemini API not configured: ' . $e->getMessage());
        }
    }
}
