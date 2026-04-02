<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGeminiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private function jsonHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }

    public function test_gemini_health_requires_authentication(): void
    {
        $response = $this->getJson('/admin/gemini/health', $this->jsonHeaders());

        $response->assertStatus(401);
        $response->assertHeader('content-type', 'application/json');
        $this->assertStringNotContainsString('<!DOCTYPE', $response->getContent());
    }

    public function test_gemini_stats_requires_authentication(): void
    {
        $response = $this->getJson('/admin/gemini/stats', $this->jsonHeaders());

        $response->assertStatus(401);
        $response->assertHeader('content-type', 'application/json');
        $this->assertStringNotContainsString('<!DOCTYPE', $response->getContent());
    }

    public function test_gemini_health_returns_json_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/admin/gemini/health', $this->jsonHeaders());

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');
        $response->assertJsonStructure([
            'success',
            'healthy',
            'message',
            'details',
            'timestamp',
        ]);
        $this->assertStringNotContainsString('<!DOCTYPE', $response->getContent());
        $this->assertTrue($response->json('success'));
    }

    public function test_gemini_stats_returns_json_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/admin/gemini/stats', $this->jsonHeaders());

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');
        $response->assertJsonStructure([
            'success',
            'stats' => [
                'total_articles',
                'gemini_processed',
                'recent_articles',
                'queue_pending',
                'service_healthy',
            ],
            'timestamp',
        ]);
        $this->assertStringNotContainsString('<!DOCTYPE', $response->getContent());
        $this->assertTrue($response->json('success'));
    }

    public function test_gemini_enhanced_page_loads_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/gemini/enhanced');

        $response->assertOk();
        $response->assertSee('/build/assets/', false);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $response->getContent());
    }
}
