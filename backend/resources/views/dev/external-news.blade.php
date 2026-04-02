<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traer Noticias Externas - Diario Malleco</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .glass-effect { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
        .dev-badge { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen py-8">
        <div class="max-w-5xl mx-auto px-4">
            <!-- Header -->
            <div class="bg-white shadow rounded-xl p-6 mb-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h1 class="text-2xl font-bold text-gray-900">
                                <i class="fas fa-rss text-red-600 mr-2"></i>
                                Traer Noticias Externas
                            </h1>
                            <span class="dev-badge">Máx. 30</span>
                        </div>
                        <p class="text-gray-600 text-sm">Busca noticias de Malleco7, SoyChile, La Discusión, La Tercera y CIPER. Selecciona las que quieras publicar y confirma.</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ url('/dev/dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                        <a href="{{ url('/dev/gemini/enhanced') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-pen mr-2"></i>Crear noticia local (IA)
                        </a>
                        <a href="http://localhost:3000" target="_blank" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-home mr-2"></i>Ver portada
                        </a>
                    </div>
                </div>
            </div>

            <!-- Buscar + Importar -->
            <div class="bg-white shadow rounded-xl p-6 mb-6">
                <div class="flex flex-wrap gap-3">
                    <button id="btnFetch" type="button" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-search mr-2"></i>Buscar noticias
                    </button>
                    <button id="btnImport" type="button" disabled class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-check mr-2"></i>Importar seleccionadas
                    </button>
                    <button id="btnSelectAll" type="button" class="hidden px-4 py-2 text-gray-600 hover:text-gray-900">
                        Seleccionar todas
                    </button>
                </div>
                <p id="statusMsg" class="mt-3 text-sm text-gray-500"></p>
            </div>

            <!-- Lista de noticias -->
            <div id="newsList" class="space-y-3">
                <div id="emptyState" class="text-center py-12 bg-white rounded-xl shadow text-gray-500">
                    <i class="fas fa-rss text-4xl mb-3 opacity-50"></i>
                    <p>Haz clic en <strong>Buscar noticias</strong> para ver las últimas de los medios externos.</p>
                </div>
                <div id="itemsContainer" class="hidden"></div>
            </div>

            <!-- Toast éxito -->
            <div id="toast" class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg hidden z-50">
                <i class="fas fa-check-circle mr-2"></i><span id="toastMsg"></span>
            </div>
        </div>
    </div>

    <script>
        const btnFetch = document.getElementById('btnFetch');
        const btnImport = document.getElementById('btnImport');
        const btnSelectAll = document.getElementById('btnSelectAll');
        const statusMsg = document.getElementById('statusMsg');
        const emptyState = document.getElementById('emptyState');
        const itemsContainer = document.getElementById('itemsContainer');
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMsg');

        let previewItems = [];

        btnFetch.addEventListener('click', async () => {
            btnFetch.disabled = true;
            statusMsg.textContent = 'Buscando noticias...';
            emptyState.classList.add('hidden');
            itemsContainer.classList.add('hidden');
            itemsContainer.innerHTML = '';

            try {
                const res = await fetch('{{ url("/dev/news/external/fetch") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({})
                });
                const data = await res.json();

                if (data.success && data.items && data.items.length > 0) {
                    previewItems = data.items;
                    renderItems(data.items);
                    statusMsg.textContent = data.message || `Se encontraron ${data.total} noticias. Selecciona las que quieras importar.`;
                    btnSelectAll.classList.remove('hidden');
                } else {
                    emptyState.classList.remove('hidden');
                    emptyState.innerHTML = '<i class="fas fa-inbox text-4xl mb-3 opacity-50"></i><p>' + (data.message || 'No hay noticias nuevas.') + '</p>';
                    statusMsg.textContent = data.message || 'No hay noticias nuevas.';
                }
            } catch (e) {
                statusMsg.textContent = 'Error: ' + e.message;
                emptyState.classList.remove('hidden');
            }
            btnFetch.disabled = false;
        });

        function renderItems(items) {
            itemsContainer.innerHTML = items.map((item, i) => `
                <div class="bg-white rounded-xl shadow p-4 flex gap-4 items-start border border-gray-100 hover:border-red-200 transition">
                    <input type="checkbox" class="item-check mt-1 rounded border-gray-300 text-red-600 focus:ring-red-500" data-idx="${i}">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 line-clamp-2">${escapeHtml(item.title)}</h3>
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">${escapeHtml(item.excerpt || '')}</p>
                        <a href="${escapeHtml(item.link)}" target="_blank" class="text-xs text-red-600 hover:underline mt-1 inline-block">Ver original</a>
                    </div>
                    ${item.image_url ? `<img src="${escapeHtml(item.image_url)}" alt="" class="w-20 h-14 object-cover rounded flex-shrink-0">` : ''}
                </div>
            `).join('');
            itemsContainer.classList.remove('hidden');
            updateImportButton();
            itemsContainer.querySelectorAll('.item-check').forEach(cb => cb.addEventListener('change', updateImportButton));
        }

        function updateImportButton() {
            const checked = itemsContainer.querySelectorAll('.item-check:checked');
            btnImport.disabled = checked.length === 0;
        }

        btnSelectAll.addEventListener('click', () => {
            const all = itemsContainer.querySelectorAll('.item-check');
            const anyUnchecked = Array.from(all).some(c => !c.checked);
            all.forEach(c => c.checked = anyUnchecked);
            updateImportButton();
        });

        btnImport.addEventListener('click', async () => {
            const checked = itemsContainer.querySelectorAll('.item-check:checked');
            const selected = Array.from(checked).map(cb => previewItems[parseInt(cb.dataset.idx)]);
            if (selected.length === 0) return;

            btnImport.disabled = true;
            statusMsg.textContent = 'Importando ' + selected.length + ' noticias...';

            try {
                const res = await fetch('{{ url("/dev/news/external/import") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ items: selected })
                });
                const data = await res.json();

                if (data.success) {
                    toastMsg.textContent = data.message + ' Haz clic en "Ver portada" para actualizar.';
                    toast.classList.remove('hidden');
                    setTimeout(() => toast.classList.add('hidden'), 5000);
                    statusMsg.textContent = data.message;
                    previewItems = previewItems.filter((_, i) => !Array.from(checked).some(c => parseInt(c.dataset.idx) === i));
                    renderItems(previewItems);
                    if (previewItems.length === 0) {
                        emptyState.classList.remove('hidden');
                        emptyState.innerHTML = '<i class="fas fa-check-circle text-4xl mb-3 text-green-500"></i><p>Noticias importadas. <a href="http://localhost:3000" target="_blank" class="text-red-600 font-medium">Ver portada actualizada</a></p>';
                        itemsContainer.classList.add('hidden');
                    }
                } else {
                    statusMsg.textContent = data.message || 'Error al importar';
                }
            } catch (e) {
                statusMsg.textContent = 'Error: ' + e.message;
            }
            btnImport.disabled = false;
        });

        function escapeHtml(s) {
            if (!s) return '';
            const d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }
    </script>
</body>
</html>
