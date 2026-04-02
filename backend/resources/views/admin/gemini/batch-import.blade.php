<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importación Batch - Diario Malleco</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-layer-group text-purple-600 mr-2"></i>
                Importación Batch de Noticias
            </h1>
            <div class="flex gap-3">
                <a href="{{ request()->routeIs('dev.*') ? url('/dev/dashboard') : route('admin.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="http://localhost:3000" target="_blank" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-external-link-alt mr-2"></i>Ver Diario
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <!-- Estado conexión IA -->
            <div id="aiStatus" class="hidden mb-4 p-4 rounded-lg border">
                <div class="flex items-center gap-2">
                    <span id="aiStatusIcon"></span>
                    <span id="aiStatusText"></span>
                </div>
            </div>

            <form id="batchImportForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de la Fuente</label>
                    <input type="text" name="source_name" id="source_name" value="Chat AI Batch" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <p class="text-xs text-gray-500 mt-1">Ej: Twitter, Gemini, etc.</p>
                </div>
                <div class="mb-4 flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="use_ai" id="use_ai" value="1" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-sm font-medium text-gray-700">Usar IA (Gemini) para transformar</span>
                    </label>
                    <button type="button" onclick="checkAiConnection()" class="px-3 py-1.5 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                        <i class="fas fa-plug mr-1"></i>Verificar conexión IA
                    </button>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contenido Batch</label>
                    <textarea name="batch_content" id="batch_content" rows="12" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 font-mono text-sm"
                        placeholder="🚨 Título de la noticia
URL: https://ejemplo.com/noticia
Contenido: Contenido completo de la noticia...
Fuente: Nombre del medio

🚨 Otra noticia
URL: https://ejemplo.com/otra
Contenido: Otro contenido...
Fuente: Otro medio"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Pega las noticias en el formato indicado</p>
                </div>
                <div class="flex gap-3">
                    <button type="submit" id="btnDetectar" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        <i class="fas fa-search mr-2"></i>Detectar Noticias
                    </button>
                    <button type="button" onclick="clearForm()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        <i class="fas fa-eraser mr-2"></i>Limpiar
                    </button>
                    <button type="button" onclick="showFormatHelp()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        <i class="fas fa-question-circle mr-2"></i>Ver Formato
                    </button>
                </div>
            </form>

            <div id="previewSection" class="hidden mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h3 class="font-bold text-gray-800 mb-3"><i class="fas fa-eye text-blue-600 mr-2"></i>Vista Previa – Edita las noticias antes de importar</h3>
                <p id="previewCount" class="text-blue-800 font-medium mb-3"></p>
                <div id="previewContent" class="max-h-[70vh] overflow-y-auto space-y-4"></div>
                <div class="mt-4 flex gap-2">
                    <button type="button" id="btnImportar" onclick="doImport()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-upload mr-2"></i>Importar Noticias
                    </button>
                    <button type="button" onclick="backToForm()" class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600">
                        <i class="fas fa-arrow-left mr-2"></i>Volver a analizar
                    </button>
                </div>
            </div>

            <div id="resultsSection" class="hidden mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                <h3 class="font-bold text-gray-800 mb-3"><i class="fas fa-check-circle text-green-600 mr-2"></i>Resultados</h3>
                <div id="resultsContent"></div>
            </div>
        </div>

        <!-- Modal formato -->
        <div id="formatModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Formato de Importación Batch</h3>
                    <button onclick="hideFormatHelp()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>
                <pre class="bg-gray-100 p-4 rounded text-sm overflow-x-auto">🚨 Título de la noticia
URL: https://ejemplo.com/noticia
Contenido: Contenido completo de la noticia...
Fuente: Nombre del medio

🚨 Otra noticia
URL: https://ejemplo.com/otra
Contenido: Otro contenido...
Fuente: Otro medio</pre>
                <ul class="mt-4 text-sm text-gray-600 space-y-1">
                    <li>• Cada noticia debe comenzar con 🚨</li>
                    <li>• URL es opcional</li>
                    <li>• Contenido mínimo 50 caracteres</li>
                    <li>• Separar cada noticia con línea en blanco</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        const parseUrl = '{{ url("/api/v1/batch-parse") }}';
        const importUrl = '{{ url("/api/v1/batch-import") }}';
        const transformUrl = '{{ url("/api/v1/transform-article") }}';
        const healthUrl = '{{ url("/api/v1/gemini-health") }}';
        const dashboardUrl = '{{ request()->routeIs("dev.*") ? url("/dev/dashboard") : route("admin.dashboard") }}';

        document.getElementById('batchImportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            detectNews();
        });

        function showFormatHelp() {
            document.getElementById('formatModal').classList.remove('hidden');
        }
        function hideFormatHelp() {
            document.getElementById('formatModal').classList.add('hidden');
        }

        function clearForm() {
            document.getElementById('batchImportForm').reset();
            document.getElementById('previewSection').classList.add('hidden');
            document.getElementById('resultsSection').classList.add('hidden');
            window.currentArticles = null;
        }

        async function checkAiConnection() {
            const statusDiv = document.getElementById('aiStatus');
            const iconSpan = document.getElementById('aiStatusIcon');
            const textSpan = document.getElementById('aiStatusText');
            statusDiv.classList.remove('hidden');
            iconSpan.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-500"></i>';
            textSpan.textContent = 'Verificando...';
            try {
                const res = await fetch(healthUrl);
                const data = await res.json();
                if (data.healthy) {
                    statusDiv.className = 'mb-4 p-4 rounded-lg border border-green-200 bg-green-50';
                    iconSpan.innerHTML = '<i class="fas fa-check-circle text-green-600"></i>';
                    textSpan.textContent = data.message + ' (Modelo: ' + (data.model || 'N/A') + ')';
                } else {
                    statusDiv.className = 'mb-4 p-4 rounded-lg border border-red-200 bg-red-50';
                    iconSpan.innerHTML = '<i class="fas fa-times-circle text-red-600"></i>';
                    textSpan.textContent = (data.message || data.error || 'Sin conexión');
                }
            } catch (e) {
                statusDiv.className = 'mb-4 p-4 rounded-lg border border-red-200 bg-red-50';
                iconSpan.innerHTML = '<i class="fas fa-times-circle text-red-600"></i>';
                textSpan.textContent = 'Error: ' + e.message;
            }
        }

        async function detectNews() {
            const content = document.getElementById('batch_content').value.trim();
            if (!content) {
                alert('Por favor ingresa el contenido batch');
                return;
            }

            const btn = document.getElementById('btnDetectar');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Detectando...';

            try {
                const formData = new FormData(document.getElementById('batchImportForm'));
                const res = await fetch(parseUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (parseErr) {
                    console.error('Status:', res.status, 'Response:', text);
                    alert('El servidor devolvió una respuesta inválida (no es JSON).\n\nStatus: ' + res.status);
                    return;
                }

                if (data.success) {
                    showPreview(data.articles);
                } else {
                    alert('Error: ' + (data.error || 'Error desconocido'));
                }
            } catch (e) {
                alert('Error de conexión: ' + e.message);
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-search mr-2"></i>Detectar Noticias';
        }

        function escapeHtml(s) {
            if (!s) return '';
            const d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }
        function showPreview(articles) {
            window.currentArticles = articles;
            document.getElementById('previewCount').textContent = 'Se detectaron ' + articles.length + ' noticias. Edita los campos si lo deseas y luego importa.';
            const html = articles.map((a, i) => `
                <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm" data-index="${i}">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-semibold text-gray-500">#${i + 1}</span>
                    </div>
                    <div class="space-y-6">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <label class="text-xs font-medium text-gray-700">Título</label>
                                <button type="button" onclick="transformWithAi(${i}, 'title')" class="btn-ai-title px-2 py-1 text-xs bg-purple-100 text-purple-700 rounded hover:bg-purple-200 transition" title="Transformar solo el título con IA">
                                    <i class="fas fa-magic mr-1"></i>IA
                                </button>
                            </div>
                            <input type="text" class="article-title w-full px-3 py-2 border rounded-lg text-sm" value="${escapeHtml(a.title)}" placeholder="Título de la noticia">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Fuente</label>
                            <input type="text" class="article-source w-full px-3 py-2 border rounded-lg text-sm" value="${escapeHtml(a.source || '')}" placeholder="Nombre de la fuente">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">URL</label>
                            <input type="text" class="article-url w-full px-3 py-2 border rounded-lg text-sm" value="${escapeHtml(a.url || '')}" placeholder="https://...">
                        </div>
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <label class="text-xs font-medium text-gray-700">Contenido</label>
                                <button type="button" onclick="transformWithAi(${i}, 'content')" class="btn-ai-content px-2 py-1 text-xs bg-purple-100 text-purple-700 rounded hover:bg-purple-200 transition" title="Transformar solo el contenido con IA">
                                    <i class="fas fa-magic mr-1"></i>IA
                                </button>
                            </div>
                            <textarea class="article-content w-full px-3 py-2 border rounded-lg text-sm" rows="6" placeholder="Contenido de la noticia">${escapeHtml(a.content || '')}</textarea>
                        </div>
                    </div>
                </div>
            `).join('');
            document.getElementById('previewContent').innerHTML = html;
            document.getElementById('previewSection').classList.remove('hidden');
        }

        async function transformWithAi(index, field) {
            const card = document.querySelector(`#previewContent [data-index="${index}"]`);
            if (!card) return;
            const titleInput = card.querySelector('.article-title');
            const contentInput = card.querySelector('.article-content');
            const title = titleInput?.value?.trim();
            const content = contentInput?.value?.trim();
            if (!title || !content || content.length < 20) {
                alert('Título y contenido (mín. 20 caracteres) son necesarios para transformar con IA.');
                return;
            }
            const btn = card.querySelector(field === 'title' ? '.btn-ai-title' : '.btn-ai-content');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Procesando...';
            try {
                const res = await fetch(transformUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ title, content })
                });
                const data = await res.json();
                if (data.success) {
                    if (field === 'title') titleInput.value = data.title || title;
                    else contentInput.value = data.content || content;
                } else {
                    alert('Error IA: ' + (data.error || 'Error desconocido'));
                }
            } catch (e) {
                alert('Error de conexión: ' + e.message);
            }
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }

        function collectEditedArticles() {
            const cards = document.querySelectorAll('#previewContent [data-index]');
            const articles = [];
            cards.forEach(card => {
                const title = card.querySelector('.article-title')?.value?.trim();
                const content = card.querySelector('.article-content')?.value?.trim();
                const source = card.querySelector('.article-source')?.value?.trim();
                const url = card.querySelector('.article-url')?.value?.trim();
                if (title && content && content.length >= 20) {
                    articles.push({ title, content, source: source || 'Chat AI Batch', url: url || '' });
                }
            });
            return articles;
        }

        async function doImport() {
            const articles = collectEditedArticles();
            if (articles.length === 0) {
                alert('No hay noticias válidas para importar. Título y contenido (mín. 20 caracteres) son obligatorios.');
                return;
            }
            const useAi = document.getElementById('use_ai').checked;
            if (useAi) {
                const ok = confirm('Con IA: cada noticia se transformará con Gemini. Puede tardar varios segundos por noticia. ¿Continuar?');
                if (!ok) return;
            }

            const btn = document.getElementById('btnImportar');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>' + (useAi ? 'Importando con IA...' : 'Importando...');

            try {
                const res = await fetch(importUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ articles, use_ai: useAi, source_name: document.getElementById('source_name').value || 'Chat AI Batch' })
                });
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (parseErr) {
                    alert('Error: respuesta inválida del servidor');
                    return;
                }
                if (data.success) {
                    showResults(data);
                } else {
                    alert('Error: ' + (data.error || 'Error desconocido'));
                }
            } catch (e) {
                alert('Error de conexión: ' + e.message);
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload mr-2"></i>Importar Noticias';
        }

        function showResults(r) {
            document.getElementById('resultsContent').innerHTML = `
                <p class="text-green-800 font-medium mb-4">${r.message}</p>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="bg-purple-600 text-white p-4 rounded text-center">
                        <p class="text-2xl font-bold">${r.articles_detected}</p>
                        <p class="text-sm">Detectadas</p>
                    </div>
                    <div class="bg-green-600 text-white p-4 rounded text-center">
                        <p class="text-2xl font-bold">${r.articles_processed}</p>
                        <p class="text-sm">Importadas</p>
                    </div>
                    <div class="bg-blue-600 text-white p-4 rounded text-center">
                        <p class="text-lg font-bold">${r.used_ai ? 'Con IA' : 'Directo'}</p>
                        <p class="text-sm">${r.used_ai ? 'Transformadas' : 'Sin transformar'}</p>
                    </div>
                </div>
                <a href="${dashboardUrl}" class="inline-block px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Volver al Dashboard</a>
            `;
            document.getElementById('resultsSection').classList.remove('hidden');
            document.getElementById('previewSection').classList.add('hidden');
        }

        function backToForm() {
            document.getElementById('previewSection').classList.add('hidden');
            document.getElementById('batch_content').focus();
        }
    </script>
</body>
</html>
