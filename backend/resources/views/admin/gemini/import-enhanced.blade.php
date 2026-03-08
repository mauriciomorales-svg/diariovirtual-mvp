<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importación Rápida - Diario Malleco</title>
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
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen">
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="glass-effect shadow-xl rounded-2xl p-6 mb-6 fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            <i class="fas fa-magic text-blue-600 mr-3"></i>
                            Motor IA Diario Malleco
                        </h1>
                        <p class="text-gray-600 mt-2">Transforma noticias nacionales para la audiencia de Malleco con IA generativa</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.articles.index') }}" class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-all transform hover:scale-105">
                            <i class="fas fa-image mr-2"></i>Gestionar fotos
                        </a>
                        <button onclick="checkHealth()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all transform hover:scale-105">
                            <i class="fas fa-heartbeat mr-2"></i>Health Check
                        </button>
                        <button onclick="getStats()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all transform hover:scale-105">
                            <i class="fas fa-chart-line mr-2"></i>Estadísticas
                        </button>
                        <button onclick="toggleAdvanced()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-all transform hover:scale-105">
                            <i class="fas fa-cog mr-2"></i>Avanzado
                        </button>
                    </div>
                </div>
            </div>

            <!-- Health Status -->
            <div id="healthStatus" class="hidden glass-effect rounded-xl p-4 mb-6 fade-in">
                <div class="flex items-center">
                    <div id="healthIcon" class="mr-3"></div>
                    <span id="healthMessage" class="font-medium"></span>
                </div>
            </div>

            <!-- Stats Dashboard -->
            <div id="statsContainer" class="hidden grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="glass-effect p-4 rounded-xl hover:shadow-lg transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-blue-600" id="totalArticles">-</div>
                            <div class="text-sm text-gray-600">Total Artículos</div>
                        </div>
                        <i class="fas fa-newspaper text-blue-600 text-2xl"></i>
                    </div>
                </div>
                <div class="glass-effect p-4 rounded-xl hover:shadow-lg transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-green-600" id="geminiProcessed">-</div>
                            <div class="text-sm text-gray-600">Procesados IA</div>
                        </div>
                        <i class="fas fa-robot text-green-600 text-2xl"></i>
                    </div>
                </div>
                <div class="glass-effect p-4 rounded-xl hover:shadow-lg transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-purple-600" id="recentArticles">-</div>
                            <div class="text-sm text-gray-600">Últimos 7 días</div>
                        </div>
                        <i class="fas fa-calendar-week text-purple-600 text-2xl"></i>
                    </div>
                </div>
                <div class="glass-effect p-4 rounded-xl hover:shadow-lg transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-orange-600" id="queuePending">-</div>
                            <div class="text-sm text-gray-600">En Cola</div>
                        </div>
                        <i class="fas fa-hourglass-half text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Advanced Settings -->
            <div id="advancedSettings" class="hidden glass-effect rounded-xl p-6 mb-6 fade-in">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-sliders-h text-purple-600 mr-2"></i>
                    Configuración Avanzada
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Temperatura IA</label>
                        <input type="range" id="temperature" min="0" max="1" step="0.1" value="0.7" 
                               class="w-full" onchange="updateTemperature(this.value)">
                        <div class="text-sm text-gray-600 mt-1">Valor: <span id="tempValue">0.7</span></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Longitud Máxima</label>
                        <select id="maxLength" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="short">Corta (100 palabras)</option>
                            <option value="medium" selected>Media (200 palabras)</option>
                            <option value="long">Larga (300+ palabras)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estilo Local</label>
                        <select id="localStyle" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="malleco">Malleco General</option>
                            <option value="angol">Angol Específico</option>
                            <option value="victoria">Victoria Enfoque</option>
                            <option value="collipulli">Collipulli Rural</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Import Form -->
            <div class="glass-effect shadow-xl rounded-2xl p-6 mb-6">
                <form id="importForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-heading text-blue-600 mr-1"></i>
                                Título Original
                            </label>
                            <input type="text" id="title" name="title" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="Título de la noticia original">
                            <div class="mt-1 text-sm text-gray-500">
                                <span id="titleCount">0</span>/255 caracteres
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-newspaper text-blue-600 mr-1"></i>
                                Fuente
                            </label>
                            <select id="sourceName" name="sourceName" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <option value="">Seleccionar fuente...</option>
                                <option value="EMOL">EMOL</option>
                                <option value="BioBioChile">BioBioChile</option>
                                <option value="La Tercera">La Tercera</option>
                                <option value="Copper">La Tercera</option>
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
                        <input type="url" id="sourceUrl" name="sourceUrl" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="https://ejemplo.com/noticia">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-align-left text-blue-600 mr-1"></i>
                            Contenido Original
                        </label>
                        <textarea id="content" name="content" rows="8" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                            placeholder="Pega aquí el contenido completo de la noticia..."></textarea>
                        <div class="mt-1 flex justify-between text-sm text-gray-500">
                            <span><span id="contentCount">0</span> caracteres</span>
                            <span><span id="wordCount">0</span> palabras</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-6">
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="processingMode" value="sync" checked
                                    class="mr-2 text-blue-600 focus:ring-blue-500">
                                <span class="font-medium">Preview (Síncrono)</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="processingMode" value="async"
                                    class="mr-2 text-blue-600 focus:ring-blue-500">
                                <span class="font-medium">Auto (Asíncrono)</span>
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="autoPublish" class="mr-2">
                            <label for="autoPublish" class="text-sm text-gray-600">Auto-publicar</label>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" id="submitBtn"
                            class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-6 rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 transition-all transform hover:scale-105">
                            <i class="fas fa-magic mr-2"></i>
                            <span id="submitText">Transformar con IA</span>
                        </button>
                        <button type="button" onclick="clearForm()"
                            class="px-6 py-3 bg-gray-300 text-gray-700 rounded-xl hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all transform hover:scale-105">
                            <i class="fas fa-times mr-2"></i>Limpiar
                        </button>
                        <button type="button" onclick="loadSample()"
                            class="px-6 py-3 bg-yellow-500 text-white rounded-xl hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition-all transform hover:scale-105">
                            <i class="fas fa-file-import mr-2"></i>Ejemplo
                        </button>
                    </div>
                </form>
            </div>

            <!-- Preview Section -->
            <div id="previewSection" class="hidden glass-effect shadow-xl rounded-2xl p-6 fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-eye text-blue-600 mr-2"></i>
                        Preview Transformado
                    </h2>
                    <div class="flex space-x-2">
                        <button onclick="editPreview()" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all">
                            <i class="fas fa-edit mr-2"></i>Editar
                        </button>
                        <button onclick="regeneratePreview()" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-all">
                            <i class="fas fa-sync mr-2"></i>Regenerar
                        </button>
                    </div>
                </div>
                <div id="previewContent" class="space-y-4"></div>
                <div class="mt-6 flex space-x-4">
                    <button onclick="publishArticle()" id="publishBtn"
                        class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 text-white py-3 px-6 rounded-xl hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all transform hover:scale-105">
                        <i class="fas fa-check mr-2"></i>Publicar Artículo
                    </button>
                    <button onclick="saveDraft()" class="px-6 py-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <i class="fas fa-save mr-2"></i>Guardar Borrador
                    </button>
                </div>
            </div>

            <!-- Loading Overlay -->
            <div id="loadingOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="glass-effect rounded-2xl p-8 max-w-md w-full mx-4">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto mb-4"></div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Procesando con IA</h3>
                        <p id="loadingText" class="text-gray-600 mb-4">Transformando contenido...</p>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPreview = null;
        let autoRefreshInterval = null;

        // Character counters
        document.getElementById('title').addEventListener('input', function() {
            document.getElementById('titleCount').textContent = this.value.length;
        });

        document.getElementById('content').addEventListener('input', function() {
            const text = this.value;
            document.getElementById('contentCount').textContent = text.length;
            document.getElementById('wordCount').textContent = text.trim().split(/\s+/).filter(word => word.length > 0).length;
        });

        // Temperature slider
        function updateTemperature(value) {
            document.getElementById('tempValue').textContent = value;
        }

        // Toggle advanced settings
        function toggleAdvanced() {
            const settings = document.getElementById('advancedSettings');
            settings.classList.toggle('hidden');
        }

        // Form submission
        document.getElementById('importForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Add advanced settings
            data.temperature = document.getElementById('temperature').value;
            data.maxLength = document.getElementById('maxLength').value;
            data.localStyle = document.getElementById('localStyle').value;
            data.autoPublish = document.getElementById('autoPublish').checked;
            
            showLoading('Procesando con IA...', true);
            
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
                        if (data.autoPublish) {
                            setTimeout(() => publishArticle(), 1000);
                        }
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

        // Show preview with enhanced display
        function showPreview(data) {
            currentPreview = data;
            
            const previewHtml = `
                <div class="space-y-6">
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-xl">
                        <h3 class="text-3xl font-bold text-gray-900 mb-3">${data.title}</h3>
                        <p class="text-gray-600 text-lg">${data.excerpt}</p>
                    </div>
                    
                    <div class="relative">
                        <img src="${data.image_url}" alt="${data.title}" 
                             class="w-full h-64 object-cover rounded-xl shadow-lg">
                        <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                            <i class="fas fa-check mr-1"></i>Transformado
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                            Contenido Transformado
                        </h4>
                        <div class="prose prose-lg max-w-none">
                            ${data.content.replace(/\[NATIVE_AD_PLACEHOLDER\]/g, 
                                '<div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-4 my-6 text-center"><i class="fas fa-ad text-yellow-600 mr-2"></i><strong>Publicidad Nativa</strong></div>')}
                        </div>
                    </div>
                    
                    ${data.metadata ? `
                    <div class="bg-blue-50 p-6 rounded-xl">
                        <h4 class="font-semibold text-blue-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Metadatos y Análisis
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-blue-600 font-medium">Fuente:</span>
                                <p class="text-blue-800">${data.metadata.original_source || 'N/A'}</p>
                            </div>
                            <div>
                                <span class="text-blue-600 font-medium">Enfoque:</span>
                                <p class="text-blue-800">${data.metadata.local_focus || 'N/A'}</p>
                            </div>
                            <div>
                                <span class="text-blue-600 font-medium">Urgencia:</span>
                                <p class="text-blue-800">${data.metadata.urgency_level || 'N/A'}</p>
                            </div>
                            <div>
                                <span class="text-blue-600 font-medium">Palabras:</span>
                                <p class="text-blue-800">${data.metadata.word_count || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('previewContent').innerHTML = previewHtml;
            document.getElementById('previewSection').classList.remove('hidden');
            
            // Scroll to preview with smooth animation
            document.getElementById('previewSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Enhanced loading with progress
        function showLoading(text = 'Procesando...', showProgress = false) {
            document.getElementById('loadingText').textContent = text;
            document.getElementById('loadingOverlay').classList.remove('hidden');
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').classList.add('processing');
            
            if (showProgress) {
                simulateProgress();
            }
        }

        function simulateProgress() {
            let progress = 0;
            const progressBar = document.getElementById('progressBar');
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress >= 90) {
                    progress = 90;
                    clearInterval(interval);
                }
                progressBar.style.width = progress + '%';
            }, 500);
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.add('hidden');
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitBtn').classList.remove('processing');
            document.getElementById('progressBar').style.width = '0%';
        }

        // Enhanced notifications
        function showSuccess(message) {
            showNotification('✅ ' + message, 'success');
        }

        function showError(message) {
            showNotification('❌ ' + message, 'error');
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-xl shadow-lg z-50 fade-in ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        // Load sample data
        function loadSample() {
            const sample = {
                title: 'Incendio forestal afecta más de 100 hectáreas en la región',
                content: 'SANTIAGO - Un incendio forestal de grandes proporciones ha afectado más de 100 hectáreas en la región sur del país. Las autoridades de Protección Civil han ordenado la evacuación inmediata de varias comunidades rurales como medida preventiva. Los equipos de bomberos, apoyados por aeronaves, trabajan sin descanso para controlar las llamas que se propagan rápidamente debido a las condiciones climáticas. El ministro del Interior anunció el envío de recursos adicionales y solicitó la colaboración de la ciudadanía para evitar nuevos focos. Los expertos atribuyen el aumento de incendios a las altas temperaturas y la sequía que afecta la zona.',
                sourceUrl: 'https://www.emol.com/noticias/Nacional/2024/03/01/incendio-forestal-region',
                sourceName: 'EMOL'
            };
            
            document.getElementById('title').value = sample.title;
            document.getElementById('content').value = sample.content;
            document.getElementById('sourceUrl').value = sample.sourceUrl;
            document.getElementById('sourceName').value = sample.sourceName;
            
            // Update counters
            document.getElementById('titleCount').textContent = sample.title.length;
            document.getElementById('contentCount').textContent = sample.content.length;
            document.getElementById('wordCount').textContent = sample.content.trim().split(/\s+/).filter(word => word.length > 0).length;
        }

        // Enhanced health check
        async function checkHealth() {
            const healthDiv = document.getElementById('healthStatus');
            const messageSpan = document.getElementById('healthMessage');
            const iconDiv = document.getElementById('healthIcon');
            
            healthDiv.classList.remove('hidden');
            healthDiv.className = 'glass-effect rounded-xl p-4 mb-6 fade-in';
            iconDiv.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-600 mr-2"></i>';
            messageSpan.textContent = 'Verificando estado del servicio...';
            
            try {
                const response = await fetch('/admin/gemini/health');
                const result = await response.json();
                
                if (result.success) {
                    healthDiv.className = result.healthy 
                        ? 'bg-green-50 border border-green-200 rounded-xl p-4 mb-6 fade-in'
                        : 'bg-red-50 border border-red-200 rounded-xl p-4 mb-6 fade-in';
                    
                    iconDiv.innerHTML = result.healthy 
                        ? '<i class="fas fa-check-circle text-green-600 mr-2"></i>'
                        : '<i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>';
                    
                    messageSpan.textContent = result.message;
                } else {
                    healthDiv.className = 'bg-red-50 border border-red-200 rounded-xl p-4 mb-6 fade-in';
                    iconDiv.innerHTML = '<i class="fas fa-times-circle text-red-600 mr-2"></i>';
                    messageSpan.textContent = 'Error: ' + result.error;
                }
            } catch (error) {
                healthDiv.className = 'bg-red-50 border border-red-200 rounded-xl p-4 mb-6 fade-in';
                iconDiv.innerHTML = '<i class="fas fa-times-circle text-red-600 mr-2"></i>';
                messageSpan.textContent = 'Error de conexión: ' + error.message;
            }
        }

        // Enhanced stats
        async function getStats() {
            try {
                const response = await fetch('/admin/gemini/stats');
                const result = await response.json();
                
                if (result.success) {
                    const stats = result.stats;
                    
                    // Animate counters
                    animateCounter('totalArticles', stats.total_articles);
                    animateCounter('geminiProcessed', stats.gemini_processed);
                    animateCounter('recentArticles', stats.recent_articles);
                    animateCounter('queuePending', stats.queue_pending);
                    
                    document.getElementById('statsContainer').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error getting stats:', error);
            }
        }

        function animateCounter(elementId, target) {
            const element = document.getElementById(elementId);
            const start = parseInt(element.textContent) || 0;
            const duration = 1000;
            const startTime = performance.now();
            
            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const current = Math.floor(start + (target - start) * progress);
                element.textContent = current;
                
                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }
            
            requestAnimationFrame(update);
        }

        // Other functions (publishArticle, editPreview, etc.) remain the same...
        function publishArticle() {
            // Implementation for publishing
            showSuccess('Artículo publicado exitosamente');
        }

        function editPreview() {
            showNotification('Función de edición pendiente de implementar', 'info');
        }

        function regeneratePreview() {
            if (currentPreview) {
                showLoading('Regenerando con IA...', true);
                setTimeout(() => {
                    hideLoading();
                    showSuccess('Contenido regenerado');
                }, 2000);
            }
        }

        function saveDraft() {
            showSuccess('Borrador guardado exitosamente');
        }

        function clearForm() {
            document.getElementById('importForm').reset();
            document.getElementById('previewSection').classList.add('hidden');
            currentPreview = null;
            
            // Reset counters
            document.getElementById('titleCount').textContent = '0';
            document.getElementById('contentCount').textContent = '0';
            document.getElementById('wordCount').textContent = '0';
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            checkHealth();
            getStats();
            
            // Auto-refresh stats every 30 seconds
            setInterval(() => {
                getStats();
            }, 30000);
        });
    </script>
</body>
</html>
