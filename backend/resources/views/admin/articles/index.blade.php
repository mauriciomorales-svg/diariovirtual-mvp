<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Noticias - Diario Malleco</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-newspaper text-red-600 mr-2"></i>
                Gestionar Noticias
            </h1>
            <div class="flex gap-3">
                <a href="{{ request()->routeIs('dev.*') ? url('/dev/dashboard') : route('admin.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="{{ request()->routeIs('dev.*') ? url('/dev/gemini/enhanced') : route('admin.gemini.enhanced') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Nueva Noticia
                </a>
                <a href="http://localhost:3000" target="_blank" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-external-link-alt mr-2"></i>Ver Diario
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Imagen</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($articles as $article)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <img src="{{ $article->image_url }}" alt="" class="w-20 h-14 object-cover rounded"
                                     onerror="this.src='{{ asset('placeholder.svg') }}'">
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-900 line-clamp-2">{{ Str::limit($article->title, 60) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $article->published_at?->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route(request()->routeIs('dev.*') ? 'dev.articles.edit-image' : 'admin.articles.edit-image', $article) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-amber-500 text-white rounded-lg hover:bg-amber-600 text-sm">
                                    <i class="fas fa-image mr-1"></i> Cambiar foto
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 bg-gray-50 border-t">
                {{ $articles->links() }}
            </div>
        </div>
    </div>
</body>
</html>
