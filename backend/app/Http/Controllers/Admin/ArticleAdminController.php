<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ImageExtractorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticleAdminController extends Controller
{
    /**
     * Lista de artículos publicados
     */
    public function index(Request $request)
    {
        $articles = Article::where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->paginate(20);

        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Formulario para editar imagen de un artículo
     */
    public function editImage(Article $article)
    {
        return view('admin.articles.edit-image', compact('article'));
    }

    /**
     * Actualiza la imagen del artículo
     */
    public function updateImage(Request $request, Article $article)
    {
        $imageUrl = null;

        if ($request->hasFile('image_file')) {
            $request->validate([
                'image_file' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            ]);
            $imageUrl = $this->saveUploadedFile($request->file('image_file'));
        } else {
            $request->validate([
                'image_url' => 'required|url',
                'download_local' => 'nullable|boolean',
            ]);
            $imageUrl = trim($request->input('image_url'));
            if ($request->boolean('download_local')) {
                $localUrl = $this->downloadAndSave($imageUrl);
                if ($localUrl) {
                    $imageUrl = $localUrl;
                }
            }
        }

        if (!$imageUrl) {
            return back()->with('error', 'No se pudo procesar la imagen.');
        }

        $article->update(['image_url' => $imageUrl]);

        Log::info('Admin updated article image', [
            'article_id' => $article->id,
            'title' => $article->title,
        ]);

        return redirect()
            ->route(request()->routeIs('dev.*') ? 'dev.articles.index' : 'admin.articles.index')
            ->with('success', 'Imagen actualizada correctamente.');
    }

    private function saveUploadedFile($file): string
    {
        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $image = $manager->read($file->getRealPath())->scaleDown(1200, 630);
        $filename = 'images/' . Str::random(40) . '.jpg';
        $path = public_path($filename);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $image->toJpeg(85)->save($path);
        return url($filename);
    }

    /**
     * Extrae imagen de la URL fuente del artículo
     */
    public function extractFromSource(Article $article)
    {
        if (!$article->external_url) {
            return back()->with('error', 'El artículo no tiene URL fuente.');
        }

        $extractor = app(ImageExtractorService::class);
        $imageUrl = $extractor->extractFromUrl($article->external_url);

        if ($imageUrl) {
            $localUrl = $this->downloadAndSave($imageUrl);
            $article->update(['image_url' => $localUrl ?? $imageUrl]);
            return back()->with('success', 'Imagen extraída y guardada.');
        }

        return back()->with('error', 'No se pudo extraer imagen de la fuente.');
    }

    private function downloadAndSave(string $url): ?string
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; DiarioVirtual/1.0)',
                    'Accept' => 'image/*',
                ])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->read($response->body())->scaleDown(1200, 630);
            $filename = 'images/' . Str::random(40) . '.jpg';
            $path = public_path($filename);
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            $image->toJpeg(85)->save($path);
            return url($filename);
        } catch (\Throwable $e) {
            Log::debug("No se pudo descargar imagen: " . $e->getMessage());
            return null;
        }
    }
}
