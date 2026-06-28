<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $cacheKey = "articles:list:page_{$page}";
        
        $articles = Cache::remember($cacheKey, 300, function () { // 5 minutes TTL
            return Article::published()
                ->orderBy('published_at', 'desc')
                ->paginate(20);
        });

        return response()->json($articles);
    }

    public function show($slug)
    {
        $cacheKey = "article:show:{$slug}";

        $article = Cache::remember($cacheKey, 1800, function () use ($slug) { // 30 minutes TTL
            return Article::where('slug', $slug)
                ->published()
                ->firstOrFail();
        });

        $this->recordArticleView($article->id);

        return response()->json($article);
    }

    /**
     * Detalle por UUID (ruta /news/{id} en el frontend cuando el slug no es válido en URL).
     */
    public function showById(string $id)
    {
        $article = Article::published()->where('id', $id)->first();

        if (! $article) {
            abort(404);
        }

        $this->recordArticleView($article->id);

        return response()->json($article);
    }

    private function recordArticleView(string $articleId): void
    {
        try {
            $updated = Article::where('id', $articleId)->increment('view_count', 1, [
                'last_viewed_at' => now(),
            ]);
            if ($updated === 0) {
                return;
            }
        } catch (\Throwable) {
            // Columna view_count puede no existir aún en entornos sin migrar
        }
    }
}
