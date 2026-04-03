<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Laravel\Facades\Image;

class ImageProxyController extends Controller
{
    private function placeholderSvg(): \Illuminate\Http\Response
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630">'
            . '<rect width="1200" height="630" fill="#1a365d"/>'
            . '<text x="600" y="315" font-family="Arial,sans-serif" font-size="48" fill="#ffffff" '
            . 'text-anchor="middle" dominant-baseline="middle">Diario Zona Sur</text>'
            . '</svg>';
        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function proxy(Request $request, string $url)
    {
        $base64 = str_replace(['-', '_'], ['+', '/'], $url);
        $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);
        $decoded = base64_decode($base64, true);
        if ($decoded === false) {
            abort(400, 'Invalid image URL');
        }
        $decodedUrl = urldecode($decoded);
        $cacheKey = 'image_proxy_' . md5($decodedUrl);

        return Cache::remember($cacheKey, 3600, function () use ($decodedUrl) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; DiarioVirtual/1.0)'])
                    ->get($decodedUrl);

                if (!$response->successful()) {
                    return $this->placeholderSvg();
                }

                $body = $response->body();
                if (empty($body)) {
                    return $this->placeholderSvg();
                }

                $contentType = $response->header('Content-Type', 'image/jpeg');

                try {
                    // scaleDown: reduce si es muy grande pero NO recorta — mantiene proporción original
                    $img = Image::read($body)->scaleDown(1200, 2400)->toWebp(82);
                    return response($img, 200)
                        ->header('Content-Type', 'image/webp')
                        ->header('Cache-Control', 'public, max-age=86400');
                } catch (\Throwable $e) {
                    return response($body, 200)
                        ->header('Content-Type', $contentType)
                        ->header('Cache-Control', 'public, max-age=86400');
                }
            } catch (\Throwable $e) {
                return $this->placeholderSvg();
            }
        });
    }
}
