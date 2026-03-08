<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\ImageExtractorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class RefreshArticleImagesCommand extends Command
{
    protected $signature = 'articles:refresh-images 
        {--limit=20 : Máximo de artículos a procesar}
        {--download : También descargar imágenes externas a local}';
    protected $description = 'Extrae imágenes reales y las guarda localmente';

    public function handle(ImageExtractorService $extractor): int
    {
        $limit = (int) $this->option('limit');
        $query = Article::whereNotNull('external_url');
        if (!$this->option('download')) {
            $query->where('image_url', 'like', '%via.placeholder%');
        } else {
            $query->where('image_url', 'like', '%http%')
                ->where('image_url', 'not like', '%/images/%')
                ->where('image_url', 'not like', '%127.0.0.1%')
                ->where('image_url', 'not like', '%localhost%');
        }
        $articles = $query->orderBy('created_at', 'desc')->limit($limit)->get();

        if ($articles->isEmpty()) {
            $this->info('No hay artículos para actualizar.');
            return 0;
        }

        $this->info("Procesando {$articles->count()} artículos (descargando imágenes localmente)...");
        $updated = 0;

        foreach ($articles as $article) {
            $imgUrl = $extractor->extractFromUrl($article->external_url);
            if (!$imgUrl && !str_contains($article->image_url ?? '', 'via.placeholder')) {
                $imgUrl = $article->image_url; // ya tiene URL externa
            }
            if ($imgUrl && filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                $localUrl = $this->downloadAndSave($imgUrl);
                $finalUrl = $localUrl ?? $imgUrl;
                $article->update(['image_url' => $finalUrl]);
                $updated++;
                $this->line('  ✓ ' . Str::limit($article->title, 60));
            } else {
                $this->line('  ✗ ' . Str::limit($article->title, 60) . ' (sin imagen)');
            }
            usleep(400000);
        }

        $this->info("Actualizados: {$updated}/{$articles->count()}");
        return 0;
    }

    private function downloadAndSave(string $url): ?string
    {
        try {
            $response = Http::timeout(12)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; DiarioVirtual/1.0)',
                    'Accept' => 'image/*',
                ])
                ->get($url);
            if (!$response->successful()) {
                return null;
            }
            $manager = new ImageManager(new Driver());
            $image = $manager->read($response->body())->scaleDown(1200, 630);
            $filename = 'images/' . Str::random(40) . '.jpg';
            $path = public_path($filename);
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            $image->toJpeg(85)->save($path);
            return url($filename);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
