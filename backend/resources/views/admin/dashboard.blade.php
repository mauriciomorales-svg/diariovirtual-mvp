<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Diario Malleco</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-tachometer-alt text-red-600 mr-2"></i>
                    Panel de Administración
                </h1>
                <p class="text-gray-500 mt-1">El Diario de Malleco</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if(isset($isDev) && $isDev)
                    <a href="{{ url('/dev/gemini/enhanced') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-pen mr-2"></i>Crear Noticia (IA)
                    </a>
                    <a href="{{ url('/dev/news/external') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-rss mr-2"></i>Traer Externas
                    </a>
                    <a href="{{ url('/dev/articles') }}" class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600">
                        <i class="fas fa-image mr-2"></i>Gestionar Fotos
                    </a>
                @else
                    <a href="{{ route('admin.gemini.enhanced') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-pen mr-2"></i>Crear Noticia (IA)
                    </a>
                    <a href="{{ route('admin.articles.index') }}" class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600">
                        <i class="fas fa-image mr-2"></i>Gestionar Fotos
                    </a>
                @endif
                <a href="http://localhost:3000" target="_blank" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <i class="fas fa-external-link-alt mr-2"></i>Ver Diario
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-red-600">
                <p class="text-sm text-gray-500 uppercase">Total</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total']) }}</p>
                <p class="text-xs text-gray-400 mt-1">artículos</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-green-600">
                <p class="text-sm text-gray-500 uppercase">Publicados</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['published']) }}</p>
                <p class="text-xs text-gray-400 mt-1">en portada</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-amber-500">
                <p class="text-sm text-gray-500 uppercase">Borradores</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['draft']) }}</p>
                <p class="text-xs text-gray-400 mt-1">pendientes</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-blue-500">
                <p class="text-sm text-gray-500 uppercase">Hoy</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['today']) }}</p>
                <p class="text-xs text-gray-400 mt-1">publicados hoy</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-indigo-500">
                <p class="text-sm text-gray-500 uppercase">Esta semana</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['this_week']) }}</p>
                <p class="text-xs text-gray-400 mt-1">últimos 7 días</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-purple-500">
                <p class="text-sm text-gray-500 uppercase">Externas</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['external']) }}</p>
                <p class="text-xs text-gray-400 mt-1">de otros medios</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-teal-500">
                <p class="text-sm text-gray-500 uppercase">Locales</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['local']) }}</p>
                <p class="text-xs text-gray-400 mt-1">creadas con IA</p>
            </div>
        </div>

        <!-- Quick Actions + Recent -->
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Acciones rápidas -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h2 class="font-bold text-gray-800">
                        <i class="fas fa-bolt text-amber-500 mr-2"></i>Acciones rápidas
                    </h2>
                </div>
                <div class="p-6 space-y-3">
                    @if(isset($isDev) && $isDev)
                        <a href="{{ url('/dev/gemini/enhanced') }}" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition group">
                            <span class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-3 group-hover:bg-blue-200">
                                <i class="fas fa-magic"></i>
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">Crear noticia con IA (Enhanced)</p>
                                <p class="text-sm text-gray-500">Transforma URL o texto en noticia local con formato</p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                        </a>
                        <a href="{{ url('/dev/gemini/import') }}" class="flex items-center p-3 rounded-lg hover:bg-indigo-50 transition group">
                            <span class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3 group-hover:bg-indigo-200">
                                <i class="fas fa-bolt"></i>
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">Importación rápida</p>
                                <p class="text-sm text-gray-500">Formulario simple: título, URL y fuente</p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                        </a>
                        <a href="{{ url('/dev/gemini/batch-import') }}" class="flex items-center p-3 rounded-lg hover:bg-purple-50 transition group">
                            <span class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mr-3 group-hover:bg-purple-200">
                                <i class="fas fa-layer-group"></i>
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">Importación batch</p>
                                <p class="text-sm text-gray-500">Pega varias noticias en formato específico</p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                        </a>
                        <a href="{{ url('/dev/news/external') }}" class="flex items-center p-3 rounded-lg hover:bg-green-50 transition group">
                            <span class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center mr-3 group-hover:bg-green-200">
                                <i class="fas fa-rss"></i>
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">Traer noticias externas</p>
                                <p class="text-sm text-gray-500">Importa hasta 30 de Malleco7, SoyChile, etc.</p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                        </a>
                    @else
                        <a href="{{ route('admin.gemini.enhanced') }}" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition group">
                            <span class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-3 group-hover:bg-blue-200">
                                <i class="fas fa-magic"></i>
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">Crear noticia con IA (Enhanced)</p>
                                <p class="text-sm text-gray-500">Transforma URL o texto en noticia local con formato</p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                        </a>
                        <a href="{{ route('admin.gemini.import') }}" class="flex items-center p-3 rounded-lg hover:bg-indigo-50 transition group">
                            <span class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3 group-hover:bg-indigo-200">
                                <i class="fas fa-bolt"></i>
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">Importación rápida</p>
                                <p class="text-sm text-gray-500">Formulario simple: título, URL y fuente</p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                        </a>
                        <a href="{{ route('admin.gemini.batch-import') }}" class="flex items-center p-3 rounded-lg hover:bg-purple-50 transition group">
                            <span class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mr-3 group-hover:bg-purple-200">
                                <i class="fas fa-layer-group"></i>
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">Importación batch</p>
                                <p class="text-sm text-gray-500">Pega varias noticias en formato específico</p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                        </a>
                    @endif
                    <a href="{{ isset($isDev) && $isDev ? url('/dev/articles') : route('admin.articles.index') }}" class="flex items-center p-3 rounded-lg hover:bg-amber-50 transition group">
                        <span class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mr-3 group-hover:bg-amber-200">
                            <i class="fas fa-image"></i>
                        </span>
                        <div>
                            <p class="font-medium text-gray-800">Gestionar imágenes</p>
                            <p class="text-sm text-gray-500">Cambiar fotos de artículos publicados</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                    </a>
                </div>
            </div>

            <!-- Últimas noticias -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                    <h2 class="font-bold text-gray-800">
                        <i class="fas fa-newspaper text-red-600 mr-2"></i>Últimas publicadas
                    </h2>
                    <a href="{{ isset($isDev) && $isDev ? url('/dev/articles') : route('admin.articles.index') }}" class="text-sm text-red-600 hover:text-red-700 font-medium">
                        Ver todas →
                    </a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($recent as $article)
                        <a href="{{ isset($isDev) && $isDev ? route('dev.articles.edit-image', $article) : route('admin.articles.edit-image', $article) }}" class="flex items-center p-4 hover:bg-gray-50 transition">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs mr-3 {{ $article->is_external ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600' }}">
                                {{ $article->is_external ? 'E' : 'L' }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-800 truncate">{{ Str::limit($article->title, 50) }}</p>
                                <p class="text-xs text-gray-500">{{ $article->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                        </a>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 opacity-50"></i>
                            <p>No hay noticias publicadas</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-400 text-sm mt-8">
            {{ now()->format('d/m/Y H:i') }} · El Diario de Malleco
        </p>
    </div>
</body>
</html>
