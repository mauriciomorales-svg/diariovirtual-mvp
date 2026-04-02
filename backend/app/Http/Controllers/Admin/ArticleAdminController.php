<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ImageExtractorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArticleAdminController extends Controller
{
    /**
     * Lista de artículos publicados
     */
    public function index(Request $request)
    {
        $articles = Article::query()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Formulario para crear noticia manual (sin IA).
     */
    public function create()
    {
        return view('admin.articles.create');
    }

    /**
     * Guarda noticia nueva creada a mano.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'slug' => 'required|string|max:255|unique:articles,slug',
            'excerpt' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'image_url' => 'nullable|string|max:2000',
            'is_external' => 'sometimes|boolean',
            'external_url' => 'nullable|string|max:2000',
            'status' => 'required|in:published,draft,scheduled',
            'published_at' => 'nullable|date',
            'metadata_json' => 'nullable|string',
        ]);

        $validated['is_external'] = $request->boolean('is_external');
        $validated['source_hash'] = hash('sha256', 'manual:'.Str::uuid()->toString());

        $img = trim((string) ($validated['image_url'] ?? ''));
        if ($img === '') {
            $validated['image_url'] = 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Zona+Sur';
        } elseif (! filter_var($img, FILTER_VALIDATE_URL)) {
            return back()->withErrors(['image_url' => 'La URL de imagen no es válida.'])->withInput();
        } else {
            $validated['image_url'] = $img;
        }

        $ext = trim((string) ($validated['external_url'] ?? ''));
        if ($ext !== '') {
            if (! filter_var($ext, FILTER_VALIDATE_URL)) {
                return back()->withErrors(['external_url' => 'La URL externa no es válida.'])->withInput();
            }
            $validated['external_url'] = $ext;
        } else {
            $validated['external_url'] = null;
        }

        $excerpt = trim((string) ($validated['excerpt'] ?? ''));
        if ($excerpt === '' && ! empty($validated['content'])) {
            $excerpt = Str::limit(strip_tags($validated['content']), 252);
        }
        if ($excerpt === '') {
            $excerpt = Str::limit($validated['title'], 252);
        }
        $validated['excerpt'] = $excerpt;

        if (empty($validated['published_at'])) {
            $validated['published_at'] = $validated['status'] === 'published' ? now() : null;
        }

        unset($validated['metadata_json']);

        $article = new Article;
        $article->fill($validated);

        $metaRaw = trim((string) $request->input('metadata_json', ''));
        if ($metaRaw !== '') {
            $decoded = json_decode($metaRaw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()
                    ->withErrors(['metadata_json' => 'Metadata: JSON inválido ('.json_last_error_msg().').'])
                    ->withInput();
            }
            $article->metadata = is_array($decoded) ? $decoded : [];
        }

        $article->save();

        Log::info('Admin created article manually', ['article_id' => $article->id]);

        return redirect()
            ->route(request()->routeIs('dev.*') ? 'dev.articles.edit' : 'admin.articles.edit', $article)
            ->with('success', 'Noticia creada. Puedes seguir editando.');
    }

    /**
     * Formulario de edición completa (título, texto, imagen, fechas, etc.)
     */
    public function edit(Article $article)
    {
        $metadataJson = '';
        if ($article->metadata !== null && $article->metadata !== []) {
            $metadataJson = json_encode($article->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return view('admin.articles.edit', compact('article', 'metadataJson'));
    }

    /**
     * Guarda cambios del artículo
     */
    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('articles', 'slug')->ignore($article->id),
            ],
            'excerpt' => 'nullable|string|max:5000',
            'content' => 'nullable|string',
            'image_url' => 'nullable|string|max:2000',
            'is_external' => 'sometimes|boolean',
            'external_url' => 'nullable|string|max:2000',
            'status' => 'required|in:published,draft,scheduled',
            'published_at' => 'nullable|date',
            'metadata_json' => 'nullable|string',
        ]);

        $validated['is_external'] = $request->boolean('is_external');

        $img = isset($validated['image_url']) ? trim((string) $validated['image_url']) : '';
        if ($img !== '') {
            if (! filter_var($img, FILTER_VALIDATE_URL)) {
                return back()->withErrors(['image_url' => 'La URL de imagen no es válida.'])->withInput();
            }
            $validated['image_url'] = $img;
        } else {
            $validated['image_url'] = $article->image_url;
        }

        $ext = isset($validated['external_url']) ? trim((string) $validated['external_url']) : '';
        if ($ext !== '') {
            if (! filter_var($ext, FILTER_VALIDATE_URL)) {
                return back()->withErrors(['external_url' => 'La URL externa no es válida.'])->withInput();
            }
            $validated['external_url'] = $ext;
        } else {
            $validated['external_url'] = null;
        }

        unset($validated['metadata_json']);

        if (empty($validated['published_at'])) {
            $validated['published_at'] = null;
        }

        $article->fill($validated);

        $metaRaw = trim((string) $request->input('metadata_json', ''));
        if ($metaRaw === '') {
            $article->metadata = null;
        } else {
            $decoded = json_decode($metaRaw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()
                    ->withErrors(['metadata_json' => 'Metadata: JSON inválido ('.json_last_error_msg().').'])
                    ->withInput();
            }
            $article->metadata = is_array($decoded) ? $decoded : [];
        }

        $article->save();

        Log::info('Admin updated article', [
            'article_id' => $article->id,
            'title' => $article->title,
        ]);

        return redirect()
            ->route(request()->routeIs('dev.*') ? 'dev.articles.edit' : 'admin.articles.edit', $article)
            ->with('success', 'Noticia actualizada correctamente.');
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
