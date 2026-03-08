<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\GeminiService;
use App\Services\ImageExtractorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class GeminiBatchImportController extends Controller
{
    public function showBatchImportForm()
    {
        return view('admin.gemini.batch-import');
    }

    /**
     * Solo parsea el contenido batch y devuelve las noticias detectadas (sin importar).
     */
    public function parseBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_content' => 'required|string|min:50',
            'source_name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $batchContent = $request->input('batch_content');
            $sourceName = $request->input('source_name', 'Chat AI Batch');
            $articles = $this->parseBatchContent($batchContent, $sourceName);

            if (empty($articles)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se detectaron noticias válidas. Cada noticia debe empezar con 🚨 y tener Contenido:',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Se detectaron ' . count($articles) . ' noticias. Revisa la vista previa y edita si lo deseas antes de importar.',
                'articles' => $articles,
            ]);
        } catch (\Throwable $e) {
            Log::error('Batch parse failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transforma título y contenido de una noticia con IA (Gemini).
     */
    public function transformArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:500',
            'content' => 'required|string|min:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $health = app(GeminiService::class)->healthCheck();
            if (!$health['available']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Gemini no disponible: ' . ($health['error'] ?? 'Sin conexión'),
                ], 503);
            }

            $transformed = app(GeminiService::class)->transformArticle(
                $request->input('content'),
                $request->input('title')
            );

            return response()->json([
                'success' => true,
                'title' => $transformed['title'],
                'content' => $transformed['content'],
                'excerpt' => $transformed['excerpt'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Transform article failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Procesa el import batch. Con IA (Gemini) o directo según use_ai.
     * Acepta batch_content (parsea e importa) o articles (array ya parseado/editado).
     */
    public function processBatchImport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_content' => 'required_without:articles|string|min:50',
            'articles' => 'required_without:batch_content|array',
            'articles.*.title' => 'required_with:articles|string|max:500',
            'articles.*.content' => 'required_with:articles|string|min:20',
            'articles.*.source' => 'sometimes|string|max:100',
            'articles.*.url' => 'sometimes|nullable|string|max:500',
            'source_name' => 'sometimes|string|max:100',
            'use_ai' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $useAi = filter_var($request->input('use_ai'), FILTER_VALIDATE_BOOLEAN);
            $articles = [];

            if ($request->has('articles') && is_array($request->input('articles'))) {
                $sourceName = $request->input('source_name', 'Chat AI Batch');
                foreach ($request->input('articles') as $a) {
                    if (!empty($a['title']) && !empty($a['content']) && strlen($a['content']) >= 20) {
                        $articles[] = [
                            'title' => $a['title'],
                            'content' => $a['content'],
                            'source' => $a['source'] ?? $sourceName,
                            'url' => $a['url'] ?? '',
                        ];
                    }
                }
            } else {
                $batchContent = $request->input('batch_content');
                $sourceName = $request->input('source_name', 'Chat AI Batch');
                $articles = $this->parseBatchContent($batchContent, $sourceName);
            }

            if (empty($articles)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se detectaron noticias válidas. Cada noticia debe empezar con 🚨 y tener Contenido:',
                ], 400);
            }

            if ($useAi) {
                $health = app(GeminiService::class)->healthCheck();
                if (!$health['available']) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Gemini no disponible: ' . ($health['error'] ?? 'Sin conexión') . '. Usa "Sin IA" o verifica la API key.',
                    ], 503);
                }
            }

            $processed = $this->processArticles($articles, $useAi);

            return response()->json([
                'success' => true,
                'message' => "Se importaron {$processed['count']} noticias correctamente" . ($useAi ? ' (con IA)' : ' (directo)'),
                'articles_detected' => count($articles),
                'articles_processed' => $processed['count'],
                'used_ai' => $useAi,
                'preview' => $processed['preview'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Batch import failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function processArticles(array $articles, bool $useAi): array
    {
        $placeholder = 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco';
        $processedCount = 0;
        $preview = [];

        if ($useAi) {
            $gemini = app(GeminiService::class);
            $imageExtractor = app(ImageExtractorService::class);
        }

        foreach ($articles as $article) {
            try {
                $url = $article['url'] ?: 'https://diariomalleco.local/' . Str::random(12);
                $sourceHash = hash('sha256', $url);

                if (Article::where('source_hash', $sourceHash)->exists()) {
                    continue;
                }

                if ($useAi) {
                    $transformed = $gemini->transformArticle($article['content'], $article['title']);
                    $imageUrl = $transformed['image_url'] ?? $placeholder;
                    if (str_contains($imageUrl, 'via.placeholder')) {
                        $extracted = $imageExtractor->extractFromUrl($url);
                        if ($extracted) {
                            $imageUrl = $extracted;
                        }
                    }
                    Article::create([
                        'title' => $transformed['title'],
                        'slug' => $transformed['slug'],
                        'source_hash' => $sourceHash,
                        'excerpt' => $transformed['excerpt'],
                        'content' => $transformed['content'],
                        'image_url' => $imageUrl,
                        'is_external' => true,
                        'external_url' => $url,
                        'status' => 'published',
                        'published_at' => now(),
                    ]);
                    $preview[] = [
                        'title' => $transformed['title'],
                        'source' => $article['source'],
                        'content' => $transformed['content'],
                        'content_length' => strlen($transformed['content']),
                        'has_url' => !empty($url),
                        'url' => $url,
                        'transformed' => true,
                    ];
                } else {
                    Article::create([
                        'title' => '🚨 ' . $article['title'],
                        'slug' => Str::slug($article['title']),
                        'source_hash' => $sourceHash,
                        'excerpt' => Str::limit(strip_tags($article['content']), 252),
                        'content' => $article['content'],
                        'image_url' => $placeholder,
                        'is_external' => true,
                        'external_url' => $url,
                        'status' => 'published',
                        'published_at' => now(),
                    ]);
                    $preview[] = [
                        'title' => '🚨 ' . $article['title'],
                        'source' => $article['source'],
                        'content' => $article['content'],
                        'content_length' => strlen($article['content']),
                        'has_url' => !empty($article['url']),
                        'url' => $url,
                        'transformed' => false,
                    ];
                }
                $processedCount++;
            } catch (\Exception $e) {
                Log::warning('Batch article skip', ['title' => $article['title'], 'error' => $e->getMessage()]);
            }
        }

        return ['count' => $processedCount, 'preview' => $preview];
    }

    /**
     * Parsea el contenido batch. Marcadores: 🚨 ⚠️ ⚠
     */
    private function parseBatchContent(string $content, string $defaultSource): array
    {
        $articles = [];
        $lines = explode("\n", $content);
        $current = ['title' => '', 'url' => '', 'content' => '', 'source' => $defaultSource];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            $startsMarker = str_starts_with($line, '🚨') || str_starts_with($line, '⚠️') || str_starts_with($line, '⚠');

            if ($startsMarker) {
                if (!empty($current['title']) && !empty($current['content'])) {
                    $article = $this->normalizeArticle($current);
                    if ($article) {
                        $articles[] = $article;
                    }
                }
                $current = ['title' => '', 'url' => '', 'content' => '', 'source' => $defaultSource];
                foreach (['🚨 ', '🚨', '⚠️ ', '⚠️', '⚠ '] as $prefix) {
                    if (str_starts_with($line, $prefix)) {
                        $line = substr($line, strlen($prefix));
                        break;
                    }
                }
                $current['title'] = trim($line);
                continue;
            }
            if (str_starts_with($line, 'URL:')) {
                $current['url'] = trim(substr($line, 4));
                continue;
            }
            if (str_starts_with($line, 'Contenido:')) {
                $current['content'] = trim(substr($line, 10));
                continue;
            }
            if (str_starts_with($line, 'Fuente:')) {
                $current['source'] = trim(substr($line, 7));
                continue;
            }
            if (!empty($current['content'])) {
                $current['content'] .= ' ' . $line;
            }
        }

        if (!empty($current['title']) && !empty($current['content'])) {
            $article = $this->normalizeArticle($current);
            if ($article) {
                $articles[] = $article;
            }
        }

        return $articles;
    }

    private function normalizeArticle(array $current): ?array
    {
        if (strlen($current['content']) < 20) {
            return null;
        }
        $current['url'] = $current['url'] ?: 'https://diariomalleco.local/news/' . uniqid();
        return $current;
    }
}
