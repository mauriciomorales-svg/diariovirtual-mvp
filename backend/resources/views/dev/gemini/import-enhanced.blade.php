<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importación Rápida - Diario Malleco (DEV)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @keyframes pulse-border {
            0%, 100% { border-color: rgb(59, 130, 246); }
            50% { border-color: rgb(147, 197, 253); }
        }
        .processing { animation: pulse-border 2s infinite; }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .dev-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .debug-panel {
            background: #1a1a2e;
            color: #00ff00;
            font-family: monospace;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen">
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="glass-effect shadow-xl rounded-2xl p-6 mb-6 fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                <i class="fas fa-magic text-blue-600 mr-3"></i>
                                Motor IA Diario Malleco
                            </h1>
                            <span class="dev-badge">Modo Desarrollo</span>
                        </div>
                        <p class="text-gray-600 mt-2">Transforma noticias nacionales para la audiencia de Malleco con IA generativa</p>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="checkHealth()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all transform hover:scale-105">
                            <i class="fas fa-heartbeat mr-2"></i>Health Check
                        </button>
                        <button onclick="getStats()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all transform hover:scale-105">
                            <i class="fas fa-chart-line mr-2"></i>Estadísticas
                        </button>
                    </div>
                </div>
            </div>

            <!-- Debug Panel -->
            <div id="debugPanel" class="hidden debug-panel"></div>

            <!-- Import Form -->
            <div class="glass-effect shadow-xl rounded-2xl p-6 mb-6">
                <form id="importForm" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-heading text-blue-600 mr-1"></i>
                                Título Original
                            </label>
                            <input type="text" id="title" name="title" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="Título de la noticia original">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-newspaper text-blue-600 mr-1"></i>
                                Fuente
                            </label>
                            <select id="source_name" name="source_name" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <option value="">Seleccionar fuente...</option>
                                <option value="EMOL">EMOL</option>
                                <option value="BioBioChile">BioBioChile</option>
                                <option value="La Tercera">La Tercera</option>
                                <option value="SoyChile">SoyChile</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-link text-blue-600 mr-1"></i>
                            URL Original
                        </label>
                        <input type="url" id="source_url" name="source_url" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="https://ejemplo.com/noticia">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-align-left text-blue-600 mr-1"></i>
                            Contenido Original
                        </label>
                        <textarea id="content" name="content" rows="6" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                            placeholder="Pega aquí el contenido completo de la noticia..."></textarea>
                    </div>

                    <div class="flex items-center space-x-6">
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="processing_mode" value="sync" checked
                                    class="mr-2 text-blue-600 focus:ring-blue-500">
                                <span class="font-medium">Preview</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="processing_mode" value="async"
                                    class="mr-2 text-blue-600 focus:ring-blue-500">
                                <span class="font-medium">Auto</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" id="submitBtn"
                            class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-6 rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 transition-all">
                            <i class="fas fa-magic mr-2"></i>Transformar con IA
                        </button>
                        <button type="button" onclick="loadSample()"
                            class="px-6 py-3 bg-yellow-500 text-white rounded-xl hover:bg-yellow-600 transition-all">
                            <i class="fas fa-file-import mr-2"></i>Ejemplo
                        </button>
                    </div>
                </form>
            </div>

            <!-- Preview Section -->
            <div id="previewSection" class="hidden glass-effect shadow-xl rounded-2xl p-6 fade-in">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-eye text-blue-600 mr-2"></i>Preview Transformado
                </h2>
                <div id="previewContent" class="space-y-4"></div>
            </div>

            <!-- Loading Overlay -->
            <div id="loadingOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="glass-effect rounded-2xl p-8 max-w-md w-full mx-4 text-center">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Procesando con IA</h3>
                    <p id="loadingText" class="text-gray-600">Transformando contenido...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = '/dev/gemini';
        let currentPreview = null;

        function logDebug(message) {
            const panel = document.getElementById('debugPanel');
            panel.classList.remove('hidden');
            panel.textContent += `[${new Date().toLocaleTimeString()}] ${message}\n`;
            console.log(message);
        }

        // Get CSRF Token from the hidden input
        function getCsrfToken() {
            const tokenInput = document.querySelector('input[name="_token"]');
            return tokenInput ? tokenInput.value : '';
        }

        // Form submission with CSRF token
        document.getElementById('importForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            const csrfToken = getCsrfToken();
            
            logDebug('=== INICIANDO PETICIÓN ===');
            logDebug('CSRF Token: ' + csrfToken.substring(0, 20) + '...');
            logDebug('Datos: ' + JSON.stringify(data, null, 2));
            
            document.getElementById('loadingOverlay').classList.remove('hidden');
            document.getElementById('submitBtn').disabled = true;
            
            try {
                const url = `${API_BASE}/process`;
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                });
                
                logDebug('Status: ' + response.status);
                
                const responseText = await response.text();
                logDebug('Respuesta: ' + responseText.substring(0, 300));
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    showError('Error parsing JSON');
                    return;
                }
                
                if (result.success) {
                    showSuccess(result.message || 'Éxito');
                    if (result.data) showPreview(result.data);
                } else {
                    showError(result.error || 'Error del servidor');
                }
            } catch (error) {
                showError('Error: ' + error.message);
            } finally {
                document.getElementById('loadingOverlay').classList.add('hidden');
                document.getElementById('submitBtn').disabled = false;
            }
        });

        function showPreview(data) {
            currentPreview = data;
            const html = `
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-4 rounded-xl mb-4">
                    <h3 class="text-xl font-bold">${data.title || 'Sin título'}</h3>
                    <p class="text-gray-600">${data.excerpt || ''}</p>
                </div>
                ${data.image_url ? `<img src="${data.image_url}" class="w-full h-48 object-cover rounded-xl mb-4">` : ''}
                <div class="bg-white p-4 rounded-xl"><div class="prose">${data.content || ''}</div></div>
            `;
            document.getElementById('previewContent').innerHTML = html;
            document.getElementById('previewSection').classList.remove('hidden');
        }

        function showSuccess(msg) {
            const div = document.createElement('div');
            div.className = 'fixed top-4 right-4 p-4 rounded-xl bg-green-500 text-white z-50';
            div.textContent = '✅ ' + msg;
            document.body.appendChild(div);
            setTimeout(() => div.remove(), 5000);
        }

        function showError(msg) {
            const div = document.createElement('div');
            div.className = 'fixed top-4 right-4 p-4 rounded-xl bg-red-500 text-white z-50';
            div.textContent = '❌ ' + msg;
            document.body.appendChild(div);
            setTimeout(() => div.remove(), 8000);
        }

        function loadSample() {
            document.getElementById('title').value = 'Incendio forestal afecta más de 100 hectáreas';
            document.getElementById('content').value = 'SANTIAGO - Un incendio forestal de grandes proporciones ha afectado más de 100 hectáreas en la región sur del país.';
            document.getElementById('source_url').value = 'https://www.emol.com/noticias/Nacional/2024/03/01/incendio';
            document.getElementById('source_name').value = 'EMOL';
        }

        async function checkHealth() {
            try {
                const response = await fetch(`${API_BASE}/health`);
                const result = await response.json();
                logDebug('Health: ' + JSON.stringify(result));
            } catch (error) {
                logDebug('Health error: ' + error.message);
            }
        }

        async function getStats() {
            try {
                const response = await fetch(`${API_BASE}/stats`);
                const result = await response.json();
                logDebug('Stats: ' + JSON.stringify(result));
            } catch (error) {
                logDebug('Stats error: ' + error.message);
            }
        }

        checkHealth();
    </script>
</body>
</html>
