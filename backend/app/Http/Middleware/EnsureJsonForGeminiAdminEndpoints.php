<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fuerza cabeceras de petición AJAX/JSON en health y stats para que el middleware
 * auth devuelva 401 JSON en lugar de redirigir al login HTML (evita SyntaxError al parsear).
 */
class EnsureJsonForGeminiAdminEndpoints
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin/gemini/health', 'admin/gemini/stats')) {
            $request->headers->set('Accept', 'application/json');
            if (! $request->header('X-Requested-With')) {
                $request->headers->set('X-Requested-With', 'XMLHttpRequest');
            }
        }

        return $next($request);
    }
}
