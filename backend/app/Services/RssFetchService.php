<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Servicio para obtener noticias de feeds RSS.
 * Puede devolver preview (sin guardar) o importar a la BD.
 */
class RssFetchService
{
    protected const FEEDS = [
        'https://www.malleco7.cl/feed/',
        'https://www.soychile.cl/rss/araucania.xml',
        'https://www.ladiscusion.cl/feed/',
        'https://www.latercera.com/rss/',
        'https://ciperchile.cl/feed/',
    ];

    protected const MAX_ITEMS = 30;

    /**
     * Obtiene hasta MAX_ITEMS noticias de los feeds (preview, sin guardar).
     * Excluye las que ya existen en la BD.
     */
    public function fetchPreview(): array
    {
        $items = [];
        $seenLinks = [];

        foreach (self::FEEDS as $feedUrl) {
            if (count($items) >= self::MAX_ITEMS) {
                break;
            }

            $parsed = $this->parseFeed($feedUrl);
            foreach ($parsed as $item) {
                if (count($items) >= self::MAX_ITEMS) {
                    break;
                }
                $link = $item['link'] ?? '';
                if (!$link || isset($seenLinks[$link])) {
                    continue;
                }
                $sourceHash = hash('sha256', $link);
                if (Article::where('source_hash', $sourceHash)->exists()) {
                    continue;
                }
                $seenLinks[$link] = true;
                $items[] = array_merge($item, ['source_hash' => $sourceHash]);
            }
            usleep(500000); // 0.5s entre feeds
        }

        return array_slice($items, 0, self::MAX_ITEMS);
    }

    /**
     * Importa los items seleccionados (array de items con title, link, excerpt, etc).
     */
    public function importSelected(array $items): array
    {
        $imported = [];
        $extractor = app(ImageExtractorService::class);

        foreach ($items as $item) {
            $link = $item['link'] ?? '';
            $sourceHash = $item['source_hash'] ?? hash('sha256', $link);

            if (Article::where('source_hash', $sourceHash)->exists()) {
                continue;
            }

            $imageUrl = $item['image_url'] ?? null;
            if (!$imageUrl || str_contains($imageUrl ?? '', 'via.placeholder')) {
                $extracted = $extractor->extractFromUrl($link);
                if ($extracted) {
                    $imageUrl = $this->downloadAndSaveImage($extracted) ?? $extracted;
                }
            }
            if (!$imageUrl) {
                $imageUrl = 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco';
            }

            $article = Article::create([
                'title' => $item['title'],
                'slug' => Str::slug($item['title']),
                'source_hash' => $sourceHash,
                'excerpt' => $item['excerpt'] ?? '',
                'content' => null,
                'image_url' => $imageUrl,
                'is_external' => true,
                'external_url' => $link,
                'status' => 'published',
                'published_at' => $item['published_at'] ?? now(),
            ]);
            $imported[] = $article->title;
        }

        return $imported;
    }

    private function parseFeed(string $feedUrl): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
                'Accept' => 'application/rss+xml, application/xml, text/xml;q=0.9, */*;q=0.8',
                'Accept-Language' => 'es-CL,es;q=0.9,en;q=0.8',
                'Referer' => 'https://www.google.com/',
            ])->timeout(20)->get($feedUrl);

            if (!$response->successful()) {
                return [];
            }

            $content = $response->body();
            if (strpos($content, '<?xml') === false || strpos($content, '<rss') === false) {
                return [];
            }

            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            libxml_clear_errors();

            if ($xml === false || !isset($xml->channel->item)) {
                return [];
            }

            $items = [];
            foreach ($xml->channel->item as $item) {
                $title = (string) $item->title;
                $link = (string) $item->link;
                $description = (string) $item->description;
                $pubDate = (string) ($item->pubDate ?? $item->date ?? now());

                $excerpt = strip_tags($description);
                $excerpt = substr($excerpt, 0, 252) . (strlen($excerpt) > 252 ? '...' : '');

                $imageUrl = $this->extractImageUrl($item, $description);

                $items[] = [
                    'title' => $title,
                    'link' => $link,
                    'excerpt' => $excerpt,
                    'published_at' => $pubDate ? date('Y-m-d H:i:s', strtotime($pubDate)) : now()->format('Y-m-d H:i:s'),
                    'image_url' => $imageUrl,
                ];
            }
            return $items;
        } catch (\Exception $e) {
            Log::warning("RssFetchService parseFeed error: {$feedUrl} - " . $e->getMessage());
            return [];
        }
    }

    private function extractImageUrl($item, string $description): ?string
    {
        $rawUrl = null;

        if (isset($item->enclosure)) {
            $enc = $item->enclosure;
            $type = (string) ($enc['type'] ?? '');
            if (stripos($type, 'image') !== false) {
                $rawUrl = (string) ($enc['url'] ?? '');
            }
        }
        if (!$rawUrl && isset($item->children('media', true)->content)) {
            $media = $item->children('media', true)->content[0];
            $type = (string) ($media['type'] ?? '');
            if (stripos($type, 'image') !== false || !$type) {
                $rawUrl = (string) ($media['url'] ?? $media['medium'] ?? '');
            }
        }
        if (!$rawUrl && isset($item->children('media', true)->thumbnail)) {
            $thumb = $item->children('media', true)->thumbnail[0];
            $rawUrl = (string) ($thumb['url'] ?? '');
        }
        if (!$rawUrl && preg_match('/<img[^>]+src="([^"]+)"[^>]*>/i', $description, $m)) {
            $rawUrl = $m[1];
        }

        return ($rawUrl && filter_var($rawUrl, FILTER_VALIDATE_URL)) ? $rawUrl : null;
    }

    private function downloadAndSaveImage(string $url): ?string
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0',
                    'Accept' => 'image/webp,image/apng,image/*,*/*;q=0.8',
                    'Referer' => 'https://www.google.com/',
                ])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $manager = new ImageManager(new Driver());
            $image = $manager->read($response->body());
            $image->scaleDown(1200, 630);

            $filename = 'images/' . Str::random(40) . '.jpg';
            $storagePath = public_path($filename);
            if (!is_dir(dirname($storagePath))) {
                mkdir(dirname($storagePath), 0755, true);
            }
            $image->toJpeg(85)->save($storagePath);

            return url($filename);
        } catch (\Throwable $e) {
            Log::debug("RssFetchService downloadImage: {$url} - " . $e->getMessage());
            return null;
        }
    }
}
