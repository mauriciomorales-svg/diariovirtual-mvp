@php
    $frontendBase = rtrim(config('app.frontend_url'), '/');
    $publicUrl = isset($article) && $article->slug
        ? $frontendBase . '/' . $article->slug
        : $frontendBase;
    $isPublished = isset($article) && $article->status === 'published';
    $views = isset($article) ? (int) ($article->view_count ?? 0) : null;
    $facebookUrl = config('app.diario_facebook_url');
@endphp

<div class="mb-6 rounded-xl border border-sky-200 bg-gradient-to-br from-sky-50 to-white p-5 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
        <div>
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-route text-sky-600"></i>
                Flujo al diario
            </h2>
            <p class="text-sm text-gray-600 mt-1">Desde el editor hasta la portada pública — para comprobar si entra gente.</p>
        </div>
        @if($views !== null)
            <div class="text-right">
                <p class="text-xs uppercase tracking-wide text-gray-500">Visitas a esta noticia</p>
                <p class="text-3xl font-bold text-sky-700">{{ number_format($views) }}</p>
                @if($article->last_viewed_at)
                    <p class="text-xs text-gray-500 mt-1">Última: {{ $article->last_viewed_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 mb-4">
        <span class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 border border-gray-200 font-medium">
            <i class="fas fa-pen text-red-500"></i> Editor
        </span>
        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
        <span class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 border border-gray-200 font-medium">
            <i class="fas fa-check-circle {{ $isPublished ? 'text-green-600' : 'text-amber-500' }}"></i>
            {{ $isPublished ? 'Publicado' : 'Borrador / programado' }}
        </span>
        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
        <span class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 border border-gray-200 font-medium">
            <i class="fas fa-globe text-sky-600"></i> diariozonasur.cl
        </span>
        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
        <span class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 border border-gray-200 font-medium">
            <i class="fas fa-users text-purple-600"></i> Lectores
        </span>
    </div>

    <div class="flex flex-wrap gap-3">
        @if($isPublished)
            <a href="{{ $publicUrl }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-sky-600 text-white rounded-lg hover:bg-sky-700 font-medium text-sm">
                <i class="fas fa-external-link-alt"></i>
                Ver noticia en el diario
            </a>
        @else
            <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-200 text-gray-600 rounded-lg text-sm cursor-not-allowed" title="Publica la noticia para abrirla en el sitio">
                <i class="fas fa-lock"></i>
                Publica para ver en el diario
            </span>
        @endif
        <a href="{{ $frontendBase }}" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-sky-300 text-sky-800 rounded-lg hover:bg-sky-50 font-medium text-sm">
            <i class="fas fa-home"></i>
            Portada del diario
        </a>
        @if($facebookUrl)
            <a href="{{ $facebookUrl }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-blue-300 text-blue-800 rounded-lg hover:bg-blue-50 font-medium text-sm">
                <i class="fab fa-facebook"></i>
                Facebook Diario Zona Sur
            </a>
        @endif
    </div>

    @if(isset($article) && $isPublished)
        <p class="mt-3 text-xs text-gray-500 font-mono break-all">
            URL pública: <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="text-sky-700 hover:underline">{{ $publicUrl }}</a>
        </p>
    @endif
</div>
