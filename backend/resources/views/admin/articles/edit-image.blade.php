<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar imagen - {{ Str::limit($article->title, 40) }}</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <a href="{{ route(request()->routeIs('dev.*') ? 'dev.articles.index' : 'admin.articles.index') }}" class="text-blue-600 hover:underline mb-4 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <h1 class="text-xl font-bold text-gray-800 mb-2">Cambiar imagen de la noticia</h1>
            <p class="text-gray-600 mb-6">{{ Str::limit($article->title, 80) }}</p>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-6">
                <p class="text-sm font-medium text-gray-700 mb-2">Imagen actual:</p>
                <img src="{{ $article->image_url }}" alt="" id="previewImg"
                     class="w-full max-h-64 object-contain bg-gray-100 rounded-lg"
                     onerror="this.src='{{ asset('placeholder.svg') }}'">
            </div>

            <form action="{{ route(request()->routeIs('dev.*') ? 'dev.articles.update-image' : 'admin.articles.update-image', $article) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Opción 1: Subir desde el PC --}}
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 bg-gray-50 hover:border-amber-400 transition-colors">
                    <label class="flex flex-col items-center cursor-pointer">
                        <i class="fas fa-folder-open text-4xl text-amber-500 mb-2"></i>
                        <span class="font-medium text-gray-700 mb-1">Subir desde tu PC</span>
                        <span class="text-sm text-gray-500 mb-2">JPG, PNG, WebP o GIF (máx. 5 MB)</span>
                        <input type="file" name="image_file" id="image_file" accept="image/jpeg,image/png,image/webp,image/gif"
                               class="hidden">
                        <span class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 text-sm">
                            Elegir imagen de la galería
                        </span>
                    </label>
                    @error('image_file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <span class="flex-1 border-t border-gray-300"></span>
                    <span class="px-3 text-gray-500 text-sm">o</span>
                    <span class="flex-1 border-t border-gray-300"></span>
                </div>

                {{-- Opción 2: Pegar URL --}}
                <div>
                    <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Pegar URL de imagen
                    </label>
                    <input type="url" name="image_url" id="image_url"
                           value="{{ old('image_url', $article->image_url) }}"
                           placeholder="https://ejemplo.com/imagen.jpg"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    @error('image_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="download_local" id="download_local" value="1"
                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <label for="download_local" class="ml-2 text-sm text-gray-700">
                        Descargar y guardar localmente (recomendado)
                    </label>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                        <i class="fas fa-save mr-2"></i>Guardar imagen
                    </button>
                    @if($article->external_url)
                    <a href="{{ route(request()->routeIs('dev.*') ? 'dev.articles.extract-source' : 'admin.articles.extract-source', $article) }}"
                       class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
                       onclick="return confirm('¿Extraer imagen de la fuente original?')">
                        <i class="fas fa-download mr-2"></i>Extraer de fuente
                    </a>
                    @endif
                </div>
            </form>

            <p class="mt-4 text-sm text-gray-500">
                Puedes subir una imagen desde tu PC, pegar una URL de internet, o extraer la imagen de la fuente original.
            </p>
        </div>
    </div>

    <script>
        const previewImg = document.getElementById('previewImg');
        const placeholderSrc = '{{ asset("placeholder.svg") }}';

        document.getElementById('image_url').addEventListener('input', function() {
            document.getElementById('image_file').value = '';
            const url = this.value.trim();
            if (url && url.startsWith('http')) {
                previewImg.src = url;
                previewImg.onerror = () => previewImg.src = placeholderSrc;
            } else {
                previewImg.src = '{{ $article->image_url }}' || placeholderSrc;
            }
        });

        document.getElementById('image_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('image_url').value = '';
                const reader = new FileReader();
                reader.onload = (ev) => { previewImg.src = ev.target.result; };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
