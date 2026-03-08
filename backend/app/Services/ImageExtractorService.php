<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ImageExtractorService
{
    /**
     * Extrae la URL de la imagen principal de una página (og:image, twitter:image)
     */
    public function extractFromUrl(string $url): ?string
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; DiarioVirtual/1.0; +https://diariomalleco.cl)',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $html = $response->body();
            return $this->extractFromHtml($html, $url);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Extrae og:image o twitter:image del HTML
     */
    public function extractFromHtml(string $html, ?string $baseUrl = null): ?string
    {
        $patterns = [
            '/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i',
            '/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']twitter:image["\']/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $imgUrl = trim($m[1]);
                if (filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                    return $imgUrl;
                }
                if ($baseUrl && str_starts_with($imgUrl, '/')) {
                    $parsed = parse_url($baseUrl);
                    $base = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');
                    return rtrim($base, '/') . $imgUrl;
                }
            }
        }
        return null;
    }
}
