<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesamiento Batch - Diario Malleco</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-layer-group text-blue-600 mr-2"></i>
                            Procesamiento Batch con IA
                        </h1>
                        <p class="text-gray-600 mt-1">Transforma múltiples artículos simultáneamente</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="startMonitor()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            <i class="fas fa-play mr-2"></i>Iniciar Monitor
                        </button>
                        <button onclick="refreshStatus()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            <i class="fas fa-sync mr-2"></i>Actualizar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Dashboard -->
            <div id="statusDashboard" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-2xl font-bold text-blue-600" id="queueSize">-</div>
                    <div class="text-sm text-gray-600">En Cola</div>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-2xl font-bold text-green-600" id="processingJobs">-</div>
                    <div class="text-sm text-gray-600">Procesando</div>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-2xl font-bold text-red-600" id="failureRate">-</div>
                    <div class="text-sm text-gray-600">Tasa Fallo</div>
                </div>
            </div>

            <!-- Batch Statistics -->
            <div id="batchStats" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-2xl font-bold text-purple-600" id="totalBatches">-</div>
                    <div class="text-sm text-gray-600">Total Batches</div>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-2xl font-bold text-orange-600" id="successRate">-</div>
                    <div class="text-sm text-gray-600">Tasa Éxito</div>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-2xl font-bold text-yellow-600" id="failed24h">-</div>
                    <div class="text-sm text-gray-600">Fallos 24h</div>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-2xl font-bold text-indigo-600" id="avgAttempts">-</div>
                    <div class="text-sm text-gray-600">Intentos Promedio</div>
                </div>
            </div>

            <!-- Batch Form -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-plus-circle text-blue-600 mr-2"></i>
                    Nuevo Batch
                </h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Batch (opcional)
                    </label>
                    <input type="text" id="batchName" name="batchName"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ej: Noticias EMOL - 1 Marzo">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Artículos (JSON)
                    </label>
                    <textarea id="articlesJson" name="articlesJson" rows="8"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                        placeholder='[{"title": "Título 1", "content": "Contenido 1", "url": "https://...", "source": "EMOL"}, {"title": "Título 2", "content": "Contenido 2", "url": "https://...", "source": "BioBio"}]'></textarea>
                    <div class="mt-2 text-sm text-gray-600">
                        Formato: Array de objetos con title, content, url, source. Máximo 50 artículos.
                    </div>
                </div>

                <div class="flex space-x-4">
                    <button onclick="processBatch()" id="processBatchBtn"
                        class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                        <i class="fas fa-rocket mr-2"></i>
                        <span id="processBatchText">Procesar Batch</span>
                    </button>
                    <button onclick="loadSample()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        <i class="fas fa-file-import mr-2"></i>Cargar Ejemplo
                    </button>
                    <button onclick="clearBatchForm()" class="px-4 py-2 bg-red-300 text-red-700 rounded-md hover:bg-red-400 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <i class="fas fa-times mr-2"></i>Limpiar
                    </button>
                </div>
            </div>

            <!-- Management Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-cogs text-blue-600 mr-2"></i>
                    Gestión de Colas
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Limpiar Jobs Fallidos
                        </label>
                        <div class="flex space-x-2">
                            <input type="number" id="cleanupDays" value="7" min="1" max="30"
                                class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button onclick="cleanupFailedJobs()" class="px-3 py-1 bg-orange-600 text-white rounded hover:bg-orange-700">
                                <i class="fas fa-trash mr-1"></i>Limpiar
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Reintentar Jobs Fallidos
                        </label>
                        <div class="flex space-x-2">
                            <input type="number" id="retryLimit" value="10" min="1" max="50"
                                class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button onclick="retryFailedJobs()" class="px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                                <i class="fas fa-redo mr-1"></i>Reintentar
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Worker Manual
                        </label>
                        <button onclick="startManualWorker()" class="px-3 py-1 bg-purple-600 text-white rounded hover:bg-purple-700">
                            <i class="fas fa-play-circle mr-1"></i>Iniciar Worker
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading Overlay -->
            <div id="loadingOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
                    <div class="flex items-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                        <span id="loadingText">Procesando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let statusInterval = null;

        // Process batch
        async function processBatch() {
            const batchName = document.getElementById('batchName').value;
            const articlesJson = document.getElementById('articlesJson').value;
            
            if (!articlesJson.trim()) {
                showError('Debes ingresar los artículos en formato JSON');
                return;
            }

            let articles;
            try {
                articles = JSON.parse(articlesJson);
            } catch (e) {
                showError('JSON inválido: ' + e.message);
                return;
            }

            if (!Array.isArray(articles) || articles.length === 0) {
                showError('Los artículos deben ser un array no vacío');
                return;
            }

            if (articles.length > 50) {
                showError('Máximo 50 artículos por batch');
                return;
            }

            showLoading('Procesando batch...');
            
            try {
                const response = await fetch('/admin/gemini/batch/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({
                        batch_name: batchName,
                        articles: articles
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess(result.message);
                    clearBatchForm();
                    refreshStatus();
                } else {
                    showError(result.error || 'Error desconocido');
                }
            } catch (error) {
                showError('Error de conexión: ' + error.message);
            } finally {
                hideLoading();
            }
        }

        // Refresh status
        async function refreshStatus() {
            try {
                const response = await fetch('/admin/gemini/batch/status');
                const result = await response.json();
                
                if (result.success) {
                    updateDashboard(result.metrics);
                    updateBatchStats(result.batch_stats);
                }
            } catch (error) {
                console.error('Error refreshing status:', error);
            }
        }

        // Update dashboard
        function updateDashboard(metrics) {
            document.getElementById('queueSize').textContent = metrics.queue_size || 0;
            document.getElementById('processingJobs').textContent = metrics.processing_jobs || 0;
            document.getElementById('failureRate').textContent = (metrics.failure_rate || 0).toFixed(1) + '%';
        }

        // Update batch statistics
        function updateBatchStats(stats) {
            document.getElementById('totalBatches').textContent = stats.batches?.total || 0;
            document.getElementById('successRate').textContent = (stats.batches?.success_rate || 0).toFixed(1) + '%';
            document.getElementById('failed24h').textContent = stats.failures?.last_24h || 0;
            document.getElementById('avgAttempts').textContent = (stats.queue?.avg_attempts || 1).toFixed(1);
        }

        // Start monitor
        async function startMonitor() {
            try {
                const response = await fetch('/admin/gemini/batch/monitor', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Monitor iniciado');
                    startAutoRefresh();
                } else {
                    showError(result.error || 'Error al iniciar monitor');
                }
            } catch (error) {
                showError('Error de conexión: ' + error.message);
            }
        }

        // Cleanup failed jobs
        async function cleanupFailedJobs() {
            const days = document.getElementById('cleanupDays').value;
            
            if (!confirm(`¿Estás seguro de eliminar jobs fallidos de más de ${days} días?`)) {
                return;
            }

            try {
                const response = await fetch('/admin/gemini/batch/cleanup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({ days: parseInt(days) })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess(result.message);
                    refreshStatus();
                } else {
                    showError(result.error || 'Error al limpiar jobs');
                }
            } catch (error) {
                showError('Error de conexión: ' + error.message);
            }
        }

        // Retry failed jobs
        async function retryFailedJobs() {
            const limit = document.getElementById('retryLimit').value;
            
            if (!confirm(`¿Estás seguro de reintentar ${limit} jobs fallidos?`)) {
                return;
            }

            try {
                const response = await fetch('/admin/gemini/batch/retry', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({ limit: parseInt(limit) })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess(result.message);
                    refreshStatus();
                } else {
                    showError(result.error || 'Error al reintentar jobs');
                }
            } catch (error) {
                showError('Error de conexión: ' + error.message);
            }
        }

        // Load sample data
        function loadSample() {
            const sample = [
                {
                    "title": "Incendio forestal afecta región sur",
                    "content": "Un incendio forestal ha afectado más de 100 hectáreas en la región. Las autoridades han ordenado la evacuación de varias comunidades. Los bomberos trabajan para controlar las llamas.",
                    "url": "https://example.com/incendio-forestal",
                    "source": "EMOL"
                },
                {
                    "title": "Nueva ley de educación aprobada",
                    "content": "El Congreso aprobó una nueva ley de educación que modificará el sistema actual. Los cambios entrarán en vigor el próximo año. Los expertos analizan el impacto.",
                    "url": "https://example.com/ley-educacion",
                    "source": "BioBioChile"
                }
            ];
            
            document.getElementById('articlesJson').value = JSON.stringify(sample, null, 2);
            document.getElementById('batchName').value = 'Ejemplo Batch - ' + new Date().toLocaleString();
        }

        // Clear form
        function clearBatchForm() {
            document.getElementById('batchName').value = '';
            document.getElementById('articlesJson').value = '';
        }

        // Start manual worker
        function startManualWorker() {
            alert('Función de worker manual pendiente de implementar. Usa: php artisan gemini:queue-worker');
        }

        // Auto refresh
        function startAutoRefresh() {
            if (statusInterval) {
                clearInterval(statusInterval);
            }
            
            statusInterval = setInterval(() => {
                refreshStatus();
            }, 5000); // Actualizar cada 5 segundos
        }

        // Utility functions
        function showLoading(text = 'Procesando...') {
            document.getElementById('loadingText').textContent = text;
            document.getElementById('loadingOverlay').classList.remove('hidden');
            document.getElementById('processBatchBtn').disabled = true;
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.add('hidden');
            document.getElementById('processBatchBtn').disabled = false;
        }

        function showSuccess(message) {
            alert('✅ ' + message);
        }

        function showError(message) {
            alert('❌ ' + message);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            refreshStatus();
            startAutoRefresh();
        });
    </script>
</body>
</html>
