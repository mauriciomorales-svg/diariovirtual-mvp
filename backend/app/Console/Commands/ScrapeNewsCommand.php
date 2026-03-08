<?php

namespace App\Console\Commands;

use App\Models\Article;
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
    protected $signature = 'news:scrape';
    protected $description = 'Scrape news from RSS feeds';

    private const MAX_ITEMS = 30;

    public function handle()
    {
        $this->info('Starting news scraping (máx ' . self::MAX_ITEMS . ' items)...');

        $feeds = [
            'https://www.malleco7.cl/feed/',
            'https://www.soychile.cl/rss/araucania.xml',
            'https://www.ladiscusion.cl/feed/',
            'https://www.latercera.com/rss/',
            'https://ciperchile.cl/feed/',
        ];

        $importedCount = 0;
        foreach ($feeds as $feedUrl) {
            if ($importedCount >= self::MAX_ITEMS) {
                break;
            }
            $this->info("Processing feed: {$feedUrl}");
            $importedCount += $this->processFeed($feedUrl, self::MAX_ITEMS - $importedCount);
        }

        Log::info("News scraping completed! Imported: {$importedCount}");
        return 0;
    }

    private function processFeed($feedUrl, int $maxItems = 30): int
    {
        $imported = 0;
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
                return 0;
            }

            // Validar que sea RSS/XML y no HTML
            $content = $response->body();
            if (strpos($content, '<?xml') === false || strpos($content, '<rss') === false) {
                Log::warning("Feed no es RSS válido: {$feedUrl} - Contenido no es XML RSS");
                return 0;
            }

            // Intentar parsear con SimpleXML con manejo de errores
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            $xmlErrors = libxml_get_last_error();
            libxml_clear_errors();
            
            if ($xml === false || $xmlErrors !== false) {
                Log::error("Error parsing XML en feed: {$feedUrl} - " . ($xmlErrors ? $xmlErrors['message'] : 'Unknown error'));
                return 0;
            }
            
            if (!isset($xml->channel->item)) {
                Log::warning("Feed sin items encontrados: {$feedUrl}");
                return 0;
            }

            foreach ($xml->channel->item as $item) {
                if ($imported >= $maxItems) {
                    break;
                }
                $created = $this->processItem($item, $feedUrl);
                if ($created) {
                    $imported++;
                }
            }

            sleep(2);

        } catch (\Exception $e) {
            Log::error("Error procesando feed {$feedUrl}: " . $e->getMessage());
        }
        return $imported;
    }

    private function processItem($item, $feedUrl): bool
    {
        try {
            $title = (string) $item->title;
            $link = (string) $item->link;
            $description = (string) $item->description;
            $pubDate = (string) ($item->pubDate ?? $item->date ?? now());
            
            $sourceHash = hash('sha256', $link);
            
            if (Article::where('source_hash', $sourceHash)->exists()) {
                return false;
            }
            
            // Extraer imagen: feed primero, luego og:image de la página del artículo
            $imageUrl = $this->extractImageUrl($item, $description);
            if (!$imageUrl || str_contains($imageUrl ?? '', 'via.placeholder')) {
                $extracted = app(ImageExtractorService::class)->extractFromUrl($link);
                if ($extracted) {
                    $imageUrl = $this->downloadAndSaveImage($extracted) ?? $extracted;
                }
            }
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
            return true;
            
        } catch (\Exception $e) {
            Log::error("Error procesando item: " . $e->getMessage());
            return false;
        }
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

    /**
     * Descarga la imagen y la guarda localmente - siempre carga desde nuestro servidor
     */
    private function downloadAndSaveImage($url)
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'image/webp,image/apng,image/*,*/*;q=0.8',
                    'Referer' => 'https://www.google.com/',
                ])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $body = $response->body();
            $manager = new ImageManager(new Driver());
            $image = $manager->read($body);
            $image->scaleDown(1200, 630);

            $filename = 'images/' . Str::random(40) . '.jpg';
            $storagePath = public_path($filename);
            if (!is_dir(dirname($storagePath))) {
                mkdir(dirname($storagePath), 0755, true);
            }
            $image->toJpeg(85)->save($storagePath);

            return url($filename);
        } catch (\Throwable $e) {
            Log::debug("No se pudo descargar imagen {$url}: " . $e->getMessage());
            return null;
        }
    }
}
