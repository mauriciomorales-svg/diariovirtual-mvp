<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Laravel\Facades\Image;

class ImageProxyController extends Controller
{
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

        return Cache::remember($cacheKey, 86400, function () use ($decodedUrl) {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; DiarioVirtual/1.0)'])
                ->get($decodedUrl);

            if (!$response->successful()) {
                abort(404);
            }

            $body = $response->body();
            $contentType = $response->header('Content-Type', 'image/jpeg');

            try {
                $img = Image::read($body)
                    ->resize(1200, 630, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->toWebp(80);
                return response($img, 200)->header('Content-Type', 'image/webp');
            } catch (\Throwable $e) {
                return response($body, 200)->header('Content-Type', $contentType);
            }
        });
    }
}
