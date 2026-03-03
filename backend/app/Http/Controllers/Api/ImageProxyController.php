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
        $decodedUrl = base64_decode($url);
        $cacheKey = 'image_proxy_' . md5($decodedUrl);

        return Cache::remember($cacheKey, 86400, function () use ($decodedUrl) { // Cache 1 día
            $response = Http::timeout(10)->get($decodedUrl);

            if (!$response->successful()) {
                abort(404);
            }

            $img = Image::read($response->body())
                ->resize(1200, 630, function ($constraint) {
                    $constraint->aspectRatio(); // Mantiene proporción
                    $constraint->upsize(); // No agranda si es más chica
                })
                ->toWebp(80); // Calidad 80%

            return response($img, 200)->header('Content-Type', 'image/webp');
        });
    }
}
