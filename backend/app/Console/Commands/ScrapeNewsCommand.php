<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use GuzzleHttp\Client;

class ScrapeNewsCommand extends Command
{
    protected $signature = 'news:scrape';
    protected $description = 'Scrape news from RSS feeds';

    public function handle()
    {
        $this->info('Starting news scraping...');

        $feeds = [
            // ✅ Feeds locales Araucanía (prioridad alta)
            'https://www.malleco7.cl/feed/',              // ✅ Funciona perfectamente
            'https://www.soychile.cl/rss/araucania.xml',  // ✅ Funciona (49KB)
            'https://www.ladiscusion.cl/feed/',           // ✅ Funciona (15KB)
            
            // ✅ Feeds nacionales (alternativas a BioBioChile/EMOL)
            'https://www.latercera.com/rss/',             // ✅ 100 items - reemplaza BioBioChile
            'https://ciperchile.cl/feed/',                // ✅ 16 items - investigación periodística
        ];

        foreach ($feeds as $feedUrl) {
            $this->info("Processing feed: {$feedUrl}");
            $this->processFeed($feedUrl);
        }

        Log::info('News scraping completed!');
        return 0;
    }

    private function processFeed($feedUrl)
    {
        Log::info("Procesando feed: {$feedUrl}");
        
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36 Edg/123.0.0.0',
                'Accept' => 'application/rss+xml, application/xml, text/xml;q=0.9, */*;q=0.8',
                'Accept-Language' => 'es-CL,es;q=0.9,en;q=0.8',
                'Referer' => 'https://www.google.com/',
            ])->timeout(20)->get($feedUrl);
            
            if (!$response->successful()) {
                Log::warning("Feed falló: {$feedUrl} - Status: {$response->status()}");
                return;
            }

            // Validar que sea RSS/XML y no HTML
            $content = $response->body();
            if (strpos($content, '<?xml') === false || strpos($content, '<rss') === false) {
                Log::warning("Feed no es RSS válido: {$feedUrl} - Contenido no es XML RSS");
                return;
            }

            // Intentar parsear con SimpleXML con manejo de errores
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            $xmlErrors = libxml_get_last_error();
            libxml_clear_errors();
            
            if ($xml === false || $xmlErrors !== false) {
                Log::error("Error parsing XML en feed: {$feedUrl} - " . ($xmlErrors ? $xmlErrors['message'] : 'Unknown error'));
                return;
            }
            
            if (!isset($xml->channel->item)) {
                Log::warning("Feed sin items encontrados: {$feedUrl}");
                return;
            }

            foreach ($xml->channel->item as $item) {
                $this->processItem($item, $feedUrl);
            }

            // Delay 3s entre feeds para simular humano
            sleep(3);

        } catch (\Exception $e) {
            Log::error("Error procesando feed {$feedUrl}: " . $e->getMessage());
        }
    }

    private function processItem($item, $feedUrl)
    {
        try {
            $title = (string) $item->title;
            $link = (string) $item->link;
            $description = (string) $item->description;
            $pubDate = (string) ($item->pubDate ?? $item->date ?? now());
            
            // Generar hash único para evitar duplicados
            $sourceHash = hash('sha256', $link);
            
            // Verificar si ya existe
            $exists = \App\Models\Article::where('source_hash', $sourceHash)->exists();
            if ($exists) {
                Log::info("Artículo ya existe, saltando: {$title}");
                return;
            }
            
            // Extraer imagen
            $imageUrl = $this->processImage($description);
            if (!$imageUrl) {
                $imageUrl = 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco';
            }
            
            // Limpiar descripción para excerpt
            $excerpt = strip_tags($description);
            $excerpt = substr($excerpt, 0, 252) . '...';
            
            // Crear artículo
            \App\Models\Article::create([
                'title' => $title,
                'slug' => \Illuminate\Support\Str::slug($title),
                'source_hash' => $sourceHash,
                'excerpt' => $excerpt,
                'content' => null, // Se llenará con Gemini o al leer
                'image_url' => $imageUrl,
                'is_external' => true,
                'external_url' => $link,
                'status' => 'published',
                'published_at' => $pubDate ? date('Y-m-d H:i:s', strtotime($pubDate)) : now(),
            ]);
            
            Log::info("Artículo creado: {$title}");
            
        } catch (\Exception $e) {
            Log::error("Error procesando item: " . $e->getMessage());
        }
    }

    private function processImage($content)
    {
        // Simple image extraction from HTML
        if (preg_match('/<img[^>]+src="([^"]+)"[^>]*>/i', $content, $matches)) {
            $originalUrl = $matches[1];
            return $this->proxyAndOptimizeImage($originalUrl);
        }
        return null;
    }
    
    private function proxyAndOptimizeImage($url)
    {
        try {
            // Download image
            $client = new Client();
            $response = $client->get($url);
            
            if ($response->getStatusCode() !== 200) {
                return null;
            }
            
            // Create image manager instance with GD driver (Intervention Image v3)
            $manager = new ImageManager(new Driver());
            $image = $manager->read($response->getBody());
            
            // Resize to 1200x630 (OpenGraph standard) - maintaining aspect ratio
            $image->scaleDown(1200, 630);
            
            // Generate unique filename
            $filename = 'images/' . Str::random(40) . '.jpg';
            $storagePath = public_path($filename);
            
            // Ensure directory exists
            if (!is_dir(dirname($storagePath))) {
                mkdir(dirname($storagePath), 0755, true);
            }
            
            // Save optimized image (Intervention Image v3 syntax)
            $image->toJpeg(90)->save($storagePath);
            
            // Return public URL
            return url($filename);
            
        } catch (\Exception $e) {
            $this->error("Error processing image: " . $e->getMessage());
            return null;
        }
    }
}
