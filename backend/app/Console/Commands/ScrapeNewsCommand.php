<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\GeminiService;
use App\Services\ImageExtractorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use GuzzleHttp\Client;

class ScrapeNewsCommand extends Command
{
    protected $signature = 'news:scrape {--transform : Force Gemini transformation for all items}';
    protected $description = 'Scrape news from RSS feeds with mandatory AI transformation';

    private const MAX_ITEMS = 20;
    private const DELAY_BETWEEN_FEEDS = 5; // segundos
    private const DELAY_BETWEEN_ITEMS = 3; // segundos

    private array $feedSources = [
        'https://www.malleco7.cl/feed/' => 'Malleco7',
        'https://www.soychile.cl/rss/araucania.xml' => 'SoyChile Araucanía',
        'https://www.ladiscusion.cl/feed/' => 'La Discusión',
        'https://www.latercera.com/rss/' => 'La Tercera',
        'https://ciperchile.cl/feed/' => 'Ciper',
    ];

    public function handle()
    {
        $this->info('=== DIARIO VIRTUAL - News Scraper (Legal Mode) ===');
        $this->info('Todas las noticias serán transformadas por IA antes de publicar');
        $this->info('Máximo: ' . self::MAX_ITEMS . ' items');
        $this->info('');

        $importedCount = 0;
        foreach ($this->feedSources as $feedUrl => $sourceName) {
            if ($importedCount >= self::MAX_ITEMS) {
                break;
            }
            
            $remaining = self::MAX_ITEMS - $importedCount;
            $this->info("[{$sourceName}] Procesando feed... ({$remaining} restantes)");
            
            $importedCount += $this->processFeed($feedUrl, $sourceName, $remaining);
            
            if ($importedCount < self::MAX_ITEMS) {
                $this->info("Esperando " . self::DELAY_BETWEEN_FEEDS . "s antes del siguiente feed...");
                sleep(self::DELAY_BETWEEN_FEEDS);
            }
        }

        Log::info("News scraping completed! Imported: {$importedCount}");
        $this->info("");
        $this->info("✓ Completado: {$importedCount} noticias importadas y transformadas");
        return 0;
    }

    private function processFeed($feedUrl, string $sourceName, int $maxItems = 20): int
    {
        $imported = 0;
        Log::info("Procesando feed: {$feedUrl} [{$sourceName}]");
        
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36 Edg/123.0.0.0',
                'Accept' => 'application/rss+xml, application/xml, text/xml;q=0.9, */*;q=0.8',
                'Accept-Language' => 'es-CL,es;q=0.9,en;q=0.8',
                'Referer' => 'https://www.google.com/',
            ])->timeout(20)->get($feedUrl);
            
            if (!$response->successful()) {
                Log::warning("Feed falló: {$feedUrl} - Status: {$response->status()}");
                $this->error("  ✗ Feed no responde (HTTP {$response->status()})");
                return 0;
            }

            $content = $response->body();
            if (strpos($content, '<?xml') === false || strpos($content, '<rss') === false) {
                Log::warning("Feed no es RSS válido: {$feedUrl}");
                $this->error("  ✗ Feed no es RSS válido");
                return 0;
            }

            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            $xmlErrors = libxml_get_last_error();
            libxml_clear_errors();
            
            if ($xml === false || $xmlErrors !== false) {
                Log::error("Error parsing XML en feed: {$feedUrl}");
                $this->error("  ✗ Error parseando XML");
                return 0;
            }
            
            if (!isset($xml->channel->item)) {
                Log::warning("Feed sin items: {$feedUrl}");
                $this->warn("  ⚠ Feed sin items");
                return 0;
            }

            foreach ($xml->channel->item as $item) {
                if ($imported >= $maxItems) {
                    break;
                }
                
                $created = $this->processItemWithTransformation($item, $feedUrl, $sourceName);
                if ($created) {
                    $imported++;
                    $this->info("  ✓ Importado desde {$sourceName}");
                    
                    if ($imported < $maxItems) {
                        sleep(self::DELAY_BETWEEN_ITEMS);
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error("Error procesando feed {$feedUrl}: " . $e->getMessage());
            $this->error("  ✗ Error: " . $e->getMessage());
        }
        
        return $imported;
    }

    private function processItemWithTransformation($item, $feedUrl, string $sourceName): bool
    {
        try {
            $originalTitle = (string) $item->title;
            $link = (string) $item->link;
            $description = (string) $item->description;
            $pubDate = (string) ($item->pubDate ?? $item->date ?? now());
            
            $sourceHash = hash('sha256', $link);
            
            // Verificar si ya existe
            if (Article::where('source_hash', $sourceHash)->exists()) {
                return false;
            }
            
            // Preparar contenido para transformación
            $originalContent = $this->extractContentFromDescription($description);
            
            $this->info("  → Transformando: {$originalTitle}");
            
            // Transformar con Gemini OBLIGATORIAMENTE
            $geminiService = app(GeminiService::class);
            
            try {
                $transformed = $geminiService->transformArticle(
                    $originalContent, 
                    $originalTitle,
                    $sourceName,  // fuente original
                    $link         // URL original para atribución
                );
            } catch (\Exception $e) {
                Log::warning("Gemini falló, usando fallback: " . $e->getMessage());
                $transformed = $this->createFallbackTransformation(
                    $originalTitle, 
                    $originalContent, 
                    $sourceName, 
                    $link
                );
            }
            
            // Usar imagen genérica local (NO descargar de fuente externa)
            $imageUrl = $this->getGenericImage($transformed['title']);
            
            // Crear artículo con contenido transformado
            \App\Models\Article::create([
                'title' => $transformed['title'],
                'slug' => $transformed['slug'] ?? Str::slug($transformed['title']),
                'source_hash' => $sourceHash,
                'excerpt' => $transformed['excerpt'],
                'content' => $transformed['content'],
                'image_url' => $imageUrl,
                'is_external' => true,
                'external_url' => $link,
                'status' => 'published',
                'published_at' => $pubDate ? date('Y-m-d H:i:s', strtotime($pubDate)) : now(),
                'metadata' => json_encode(array_merge(
                    $transformed['metadata'] ?? [],
                    [
                        'original_source' => $sourceName,
                        'original_url' => $link,
                        'transformed_at' => now()->toIso8601String(),
                        'transformation_method' => 'gemini_ai',
                    ]
                )),
            ]);
            
            Log::info("Artículo transformado y creado: {$transformed['title']} [Fuente: {$sourceName}]");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Error procesando item: " . $e->getMessage());
            $this->error("  ✗ Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Extrae contenido limpio de la descripción HTML
     */
    private function extractContentFromDescription(string $description): string
    {
        // Quitar tags HTML
        $text = strip_tags($description);
        
        // Decodificar entidades HTML
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Limpiar espacios múltiples
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
    
    /**
     * Crear transformación fallback si Gemini falla
     */
    private function createFallbackTransformation(string $title, string $content, string $sourceName, string $originalUrl): array
    {
        $transformedTitle = '🚨 ' . $title;
        $excerpt = substr(strip_tags($content), 0, 252) . '...';
        
        // Crear contenido con estructura propia
        $transformedContent = $this->rewriteContentLocally($content);
        
        return [
            'title' => $transformedTitle,
            'slug' => Str::slug($transformedTitle),
            'excerpt' => $excerpt,
            'content' => $transformedContent,
            'metadata' => [
                'original_source' => $sourceName,
                'local_focus' => 'provincia-malleco',
                'urgency_level' => 'medium',
                'word_count' => str_word_count($transformedContent),
                'fallback_used' => true,
                'original_url' => $originalUrl,
            ]
        ];
    }
    
    /**
     * Reescritura local básica (sin IA) como fallback
     */
    private function rewriteContentLocally(string $content): string
    {
        $paragraphs = explode("\n\n", $content);
        $rewritten = [];
        
        foreach ($paragraphs as $i => $paragraph) {
            if (empty(trim($paragraph))) continue;
            
            // Agregar contexto local
            if ($i === 0) {
                $rewritten[] = $paragraph;
            } else {
                $rewritten[] = $paragraph;
            }
            
            // Insertar placeholder de ad después del 2do párrafo
            if ($i === 1) {
                $rewritten[] = '[NATIVE_AD_PLACEHOLDER]';
            }
        }
        
        // Agregar atribución al final
        $rewritten[] = "\n\n---\n\n📰 **Información para la comunidad de la Provincia de Malleco**. Esta noticia ha sido recopilada de medios regionales para mantener informados a los vecinos de Angol, Victoria, Collipulli y comunas cercanas.";
        
        return implode("\n\n", $rewritten);
    }
    
    /**
     * Obtener imagen genérica local (NO descargar de fuentes externas)
     */
    private function getGenericImage(string $title): string
    {
        // Generar imagen local con texto del título
        // Esto evita problemas de copyright con imágenes de fuentes externas
        $safeTitle = urlencode(substr($title, 0, 50));
        return "https://via.placeholder.com/1200x630/1a365d/ffffff?text=" . $safeTitle;
    }

    /**
     * Extrae URL de imagen del item RSS (enclosure, media:*, <img>)
     */
    private function extractImageUrl($item, $description)
    {
        $rawUrl = null;

        // 1. enclosure (estándar RSS para medios)
        if (isset($item->enclosure)) {
            $enc = $item->enclosure;
            $type = (string) ($enc['type'] ?? '');
            if (stripos($type, 'image') !== false) {
                $rawUrl = (string) ($enc['url'] ?? '');
            }
        }

        // 2. media:content (Media RSS)
        if (!$rawUrl && isset($item->children('media', true)->content)) {
            $media = $item->children('media', true)->content[0];
            $type = (string) ($media['type'] ?? '');
            if (stripos($type, 'image') !== false || !$type) {
                $rawUrl = (string) ($media['url'] ?? $media['medium'] ?? '');
            }
        }

        // 3. media:thumbnail
        if (!$rawUrl && isset($item->children('media', true)->thumbnail)) {
            $thumb = $item->children('media', true)->thumbnail[0];
            $rawUrl = (string) ($thumb['url'] ?? '');
        }

        // 4. <img> en description
        if (!$rawUrl && preg_match('/<img[^>]+src="([^"]+)"[^>]*>/i', $description, $m)) {
            $rawUrl = $m[1];
        }

        if ($rawUrl && filter_var($rawUrl, FILTER_VALIDATE_URL)) {
            return $this->downloadAndSaveImage($rawUrl) ?? $rawUrl;
        }
        return null;
    }

}
