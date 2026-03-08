<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Services\RssFetchService;
use Illuminate\Http\Request;

class ExternalNewsController extends Controller
{
    public function showForm()
    {
        return view('dev.external-news');
    }

    /**
     * Obtiene preview de noticias externas (máx 30, sin guardar).
     */
    public function fetch(Request $request)
    {
        $service = app(RssFetchService::class);
        $items = $service->fetchPreview();

        return response()->json([
            'success' => true,
            'items' => $items,
            'total' => count($items),
            'message' => count($items) > 0
                ? 'Se encontraron ' . count($items) . ' noticias nuevas. Selecciona las que quieras importar.'
                : 'No hay noticias nuevas en los feeds. Todas ya están importadas.',
        ]);
    }

    /**
     * Importa las noticias seleccionadas.
     * Espera: { "items": [ { "title", "link", "excerpt", "published_at", "source_hash" }, ... ] }
     */
    public function import(Request $request)
    {
        $items = $request->input('items', []);
        if (empty($items) || !is_array($items)) {
            return response()->json([
                'success' => false,
                'message' => 'No se enviaron noticias para importar.',
                'imported' => [],
            ], 422);
        }

        $service = app(RssFetchService::class);
        $imported = $service->importSelected($items);

        return response()->json([
            'success' => true,
            'message' => 'Se importaron ' . count($imported) . ' noticias correctamente.',
            'imported' => $imported,
            'count' => count($imported),
        ]);
    }
}
