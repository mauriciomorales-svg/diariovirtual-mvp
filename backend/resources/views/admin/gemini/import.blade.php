<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importación Rápida - Diario Malleco</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-magic text-blue-600 mr-2"></i>
                            Importación Rápida con IA
                        </h1>
                        <p class="text-gray-600 mt-1">Transforma noticias nacionales para la audiencia de Malleco</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="checkHealth()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            <i class="fas fa-heartbeat mr-2"></i>Health Check
                        </button>
                        <button onclick="getStats()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            <i class="fas fa-chart-bar mr-2"></i>Estadísticas
                        </button>
                    </div>
                </div>
            </div>

            <!-- Health Status -->
            <div id="healthStatus" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <span id="healthMessage">Verificando estado del servicio...</span>
                </div>
            </div>

            <!-- Stats -->
            <div id="statsContainer" class="hidden grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-2xl font-bold text-blue-600" id="totalArticles">-</div>
                    <div class="text-sm text-gray-600">Total Artículos</div>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-2xl font-bold text-green-600" id="geminiProcessed">-</div>
                    <div class="text-sm text-gray-600">Procesados por IA</div>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-2xl font-bold text-purple-600" id="recentArticles">-</div>
                    <div class="text-sm text-gray-600">Últimos 7 días</div>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-2xl font-bold text-orange-600" id="queuePending">-</div>
                    <div class="text-sm text-gray-600">En Cola</div>
                </div>
            </div>

            <!-- Import Form -->
            <div class="bg-white shadow rounded-lg p-6">
                <form id="importForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Título Original
                            </label>
                            <input type="text" id="title" name="title" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Título de la noticia original">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Fuente
                            </label>
                            <input type="text" id="sourceName" name="sourceName" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ej: EMOL, BioBioChile">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            URL Original
                        </label>
                        <input type="url" id="sourceUrl" name="sourceUrl" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="https://...">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Contenido Original
                        </label>
                        <textarea id="content" name="content" rows="8" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Pega aquí el contenido completo de la noticia..."></textarea>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Modo de Procesamiento
                        </label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="processingMode" value="sync" checked
                                    class="mr-2 text-blue-600 focus:ring-blue-500">
                                <span>Preview (Síncrono)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="processingMode" value="async"
                                    class="mr-2 text-blue-600 focus:ring-blue-500">
                                <span>Publicación Automática (Asíncrono)</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" id="submitBtn"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                            <i class="fas fa-magic mr-2"></i>
                            <span id="submitText">Transformar con IA</span>
                        </button>
                        <button type="button" onclick="clearForm()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            <i class="fas fa-times mr-2"></i>Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Preview Section -->
            <div id="previewSection" class="hidden bg-white shadow rounded-lg p-6 mt-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-eye text-blue-600 mr-2"></i>Preview Transformado
                </h2>
                <div id="previewContent"></div>
                <div class="mt-6 flex space-x-4">
                    <button onclick="publishArticle()" id="publishBtn"
                        class="flex-1 bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <i class="fas fa-check mr-2"></i>Publicar Artículo
                    </button>
                    <button onclick="editPreview()"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <i class="fas fa-edit mr-2"></i>Editar
                    </button>
                </div>
            </div>

            <!-- Loading Overlay -->
            <div id="loadingOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
                    <div class="flex items-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                        <span id="loadingText">Procesando con IA...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPreview = null;

        // Form submission
        document.getElementById('importForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            showLoading('Procesando con IA...');
            
            try {
                const response = await fetch('/admin/gemini/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (result.mode === 'preview') {
                        showPreview(result.data);
                    } else {
                        showSuccess(result.message);
                        clearForm();
                    }
                } else {
                    showError(result.error || 'Error desconocido');
                }
            } catch (error) {
                showError('Error de conexión: ' + error.message);
            } finally {
                hideLoading();
            }
        });

        // Show preview
        function showPreview(data) {
            currentPreview = data;
            
            const previewHtml = `
                <div class="space-y-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">${data.title}</h3>
                        <p class="text-gray-600 mt-2">${data.excerpt}</p>
                    </div>
                    
                    <div>
                        <img src="${data.image_url}" alt="${data.title}" class="w-full h-48 object-cover rounded">
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Contenido Transformado:</h4>
                        <div class="bg-gray-50 p-4 rounded whitespace-pre-wrap">${data.content}</div>
                    </div>
                    
                    ${data.metadata ? `
                    <div class="bg-blue-50 p-4 rounded">
                        <h4 class="font-semibold text-blue-900 mb-2">Metadatos:</h4>
                        <div class="text-sm text-blue-800">
                            <p><strong>Fuente:</strong> ${data.metadata.original_source || 'N/A'}</p>
                            <p><strong>Enfoque:</strong> ${data.metadata.local_focus || 'N/A'}</p>
                            <p><strong>Urgencia:</strong> ${data.metadata.urgency_level || 'N/A'}</p>
                            <p><strong>Palabras:</strong> ${data.metadata.word_count || 'N/A'}</p>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('previewContent').innerHTML = previewHtml;
            document.getElementById('previewSection').classList.remove('hidden');
            
            // Scroll to preview
            document.getElementById('previewSection').scrollIntoView({ behavior: 'smooth' });
        }

        // Publish article
        async function publishArticle() {
            if (!currentPreview) return;
            
            const formData = new FormData(document.getElementById('importForm'));
            const publishData = {
                title: currentPreview.title,
                slug: currentPreview.slug,
                excerpt: currentPreview.excerpt,
                content: currentPreview.content,
                image_url: currentPreview.image_url,
                source_url: formData.get('sourceUrl'),
                source_name: formData.get('sourceName')
            };
            
            showLoading('Publicando artículo...');
            
            try {
                const response = await fetch('/admin/gemini/publish', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify(publishData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Artículo publicado exitosamente');
                    clearForm();
                    document.getElementById('previewSection').classList.add('hidden');
                    currentPreview = null;
                } else {
                    showError(result.error || 'Error al publicar');
                }
            } catch (error) {
                showError('Error de conexión: ' + error.message);
            } finally {
                hideLoading();
            }
        }

        // Health check
        async function checkHealth() {
            const healthDiv = document.getElementById('healthStatus');
            const messageSpan = document.getElementById('healthMessage');
            
            healthDiv.classList.remove('hidden');
            messageSpan.textContent = 'Verificando estado del servicio...';
            
            try {
                const response = await fetch('/admin/gemini/health');
                const result = await response.json();
                
                if (result.success) {
                    healthDiv.className = result.healthy 
                        ? 'bg-green-50 border border-green-200 rounded-lg p-4 mb-6'
                        : 'bg-red-50 border border-red-200 rounded-lg p-4 mb-6';
                    
                    messageSpan.textContent = result.message;
                } else {
                    healthDiv.className = 'bg-red-50 border border-red-200 rounded-lg p-4 mb-6';
                    messageSpan.textContent = 'Error: ' + result.error;
                }
            } catch (error) {
                healthDiv.className = 'bg-red-50 border border-red-200 rounded-lg p-4 mb-6';
                messageSpan.textContent = 'Error de conexión: ' + error.message;
            }
        }

        // Get stats
        async function getStats() {
            try {
                const response = await fetch('/admin/gemini/stats');
                const result = await response.json();
                
                if (result.success) {
                    const stats = result.stats;
                    document.getElementById('totalArticles').textContent = stats.total_articles;
                    document.getElementById('geminiProcessed').textContent = stats.gemini_processed;
                    document.getElementById('recentArticles').textContent = stats.recent_articles;
                    document.getElementById('queuePending').textContent = stats.queue_pending;
                    
                    document.getElementById('statsContainer').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error getting stats:', error);
            }
        }

        // Utility functions
        function showLoading(text = 'Procesando...') {
            document.getElementById('loadingText').textContent = text;
            document.getElementById('loadingOverlay').classList.remove('hidden');
            document.getElementById('submitBtn').disabled = true;
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.add('hidden');
            document.getElementById('submitBtn').disabled = false;
        }

        function showSuccess(message) {
            alert('✅ ' + message);
        }

        function showError(message) {
            alert('❌ ' + message);
        }

        function clearForm() {
            document.getElementById('importForm').reset();
            document.getElementById('previewSection').classList.add('hidden');
            currentPreview = null;
        }

        function editPreview() {
            // Implementar edición del preview si es necesario
            alert('Función de edición pendiente de implementar');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            checkHealth();
            getStats();
        });
    </script>
</body>
</html>
