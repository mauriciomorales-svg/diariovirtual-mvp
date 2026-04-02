<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nueva noticia (sin IA) — Diario Zona Sur</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <a href="{{ route(request()->routeIs('dev.*') ? 'dev.articles.index' : 'admin.articles.index') }}" class="text-blue-600 hover:underline inline-flex items-center mb-6">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>

        <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Nueva noticia (manual, sin IA)</h1>
            <p class="text-sm text-gray-600 mb-6">Completa título, texto, imagen y demás campos. No se usa Gemini.</p>

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route(request()->routeIs('dev.*') ? 'dev.articles.store' : 'admin.articles.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                    <input type="text" name="title" id="title" required maxlength="500"
                           value="{{ old('title') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
                    <input type="text" name="slug" id="slug" required maxlength="255"
                           value="{{ old('slug') }}"
                           placeholder="mi-noticia-ejemplo"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-red-500">
                    <p class="text-xs text-gray-500 mt-1">Solo minúsculas, números y guiones. Debe ser único.</p>
                </div>

                <div>
                    <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Bajada / extracto (máx. 255 caracteres)</label>
                    <textarea name="excerpt" id="excerpt" rows="3" maxlength="255"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">{{ old('excerpt') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío, se generará a partir del contenido o del título.</p>
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Contenido (HTML permitido)</label>
                    <textarea name="content" id="content" rows="18"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-red-500">{{ old('content') }}</textarea>
                </div>

                <div>
                    <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">URL de imagen principal</label>
                    <input type="text" name="image_url" id="image_url" maxlength="2000"
                           value="{{ old('image_url') }}"
                           placeholder="https://..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                    <p class="text-xs text-gray-500 mt-1">Opcional: si lo dejas vacío se usará una imagen placeholder hasta que subas una en «Editar».</p>
                </div>

                <div class="flex flex-wrap items-center gap-6">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_external" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                               @checked(old('is_external'))>
                        <span class="text-sm font-medium text-gray-700">Noticia externa (enlace a otro medio)</span>
                    </label>
                </div>

                <div>
                    <label for="external_url" class="block text-sm font-medium text-gray-700 mb-1">URL de la noticia original</label>
                    <input type="text" name="external_url" id="external_url" maxlength="2000"
                           value="{{ old('external_url') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                            <option value="published" @selected(old('status', 'published') === 'published')>Publicado</option>
                            <option value="draft" @selected(old('status') === 'draft')>Borrador</option>
                            <option value="scheduled" @selected(old('status') === 'scheduled')>Programado</option>
                        </select>
                    </div>
                    <div>
                        <label for="published_at" class="block text-sm font-medium text-gray-700 mb-1">Fecha de publicación</label>
                        <input type="datetime-local" name="published_at" id="published_at"
                               value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label for="metadata_json" class="block text-sm font-medium text-gray-700 mb-1">Metadata (JSON opcional)</label>
                    <textarea name="metadata_json" id="metadata_json" rows="6" placeholder="{}"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono text-xs focus:ring-2 focus:ring-red-500">{{ old('metadata_json') }}</textarea>
                </div>

                <div class="flex flex-wrap gap-3 pt-4 border-t">
                    <button type="submit" class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                        <i class="fas fa-plus mr-2"></i>Crear noticia
                    </button>
                    <a href="{{ route(request()->routeIs('dev.*') ? 'dev.articles.index' : 'admin.articles.index') }}" class="px-6 py-2.5 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-medium inline-flex items-center">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
