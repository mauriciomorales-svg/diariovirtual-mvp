<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\TransformNewsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GeminiBatchImportController extends Controller
{
    /**
     * Muestra el formulario de importación batch
     */
    public function showBatchImportForm()
    {
        return view('admin.gemini.batch-import');
    }

    /**
     * Procesa el import batch de noticias
     */
    public function processBatchImport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_content' => 'required|string|min:100',
            'source_name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $batchContent = $request->input('batch_content');
            $sourceName = $request->input('source_name', 'Chat AI Batch');
            
            // Parsear el contenido batch
            $articles = $this->parseBatchContent($batchContent, $sourceName);
            
            if (empty($articles)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se detectaron noticias válidas en el contenido'
                ], 400);
            }

            // Enviar a procesamiento individual
            $processedCount = 0;
            foreach ($articles as $article) {
                try {
                    TransformNewsJob::dispatch(
                        $article['content'],
                        $article['title'],
                        $article['url'],
                        $article['source']
                    )->onQueue('gemini-transform');
                    
                    $processedCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch batch article', [
                        'title' => $article['title'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Se enviaron {$processedCount} noticias a procesamiento",
                'articles_detected' => count($articles),
                'articles_processed' => $processedCount,
                'preview' => array_map(function($article) {
                    return [
                        'title' => $article['title'],
                        'source' => $article['source'],
                        'content_length' => strlen($article['content']),
                        'has_url' => !empty($article['url'])
                    ];
                }, $articles)
            ]);

        } catch (\Exception $e) {
            Log::error('Batch import failed', [
                'error' => $e->getMessage(),
                'source_name' => $sourceName
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al procesar el batch: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parsea el contenido batch detectando noticias
     */
    private function parseBatchContent(string $content, string $defaultSource): array
    {
        $articles = [];
        
        // Dividir por líneas que empiezan con 🚨
        $sections = preg_split('/(?=^🚨)/m', $content, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($sections as $section) {
            $section = trim($section);
            if (empty($section) || !str_starts_with($section, '🚨')) {
                continue;
            }

            $article = $this->parseArticleSection($section, $defaultSource);
            if ($article) {
                $articles[] = $article;
            }
        }

        return $articles;
    }

    /**
     * Parsea una sección individual de artículo
     */
    private function parseArticleSection(string $section, string $defaultSource): ?array
    {
        $lines = array_filter(array_map('trim', explode("\n", $section)));
        
        $article = [
            'title' => '',
            'url' => '',
            'content' => '',
            'source' => $defaultSource
        ];

        foreach ($lines as $line) {
            // Título (primera línea con 🚨)
            if (empty($article['title']) && str_starts_with($line, '🚨')) {
                $article['title'] = trim(str_replace('🚨', '', $line));
                continue;
            }

            // URL
            if (str_starts_with($line, 'URL:')) {
                $article['url'] = trim(str_replace('URL:', '', $line));
                continue;
            }

            // Contenido
            if (str_starts_with($line, 'Contenido:')) {
                $article['content'] = trim(str_replace('Contenido:', '', $line));
                continue;
            }

            // Fuente
            if (str_starts_with($line, 'Fuente:')) {
                $article['source'] = trim(str_replace('Fuente:', '', $line));
                continue;
            }

            // Si no tiene prefijos, es parte del contenido
            if (!empty($article['content']) && !str_starts_with($line, '🚨') && !str_starts_with($line, 'URL:') && !str_starts_with($line, 'Contenido:') && !str_starts_with($line, 'Fuente:')) {
                $article['content'] .= ' ' . $line;
            }
        }

        // Validar campos mínimos
        if (empty($article['title']) || empty($article['content'])) {
            return null;
        }

        // Si no hay URL, usar placeholder
        if (empty($article['url'])) {
            $article['url'] = 'https://diariomalleco.local/news/' . time();
        }

        return $article;
    }
}
