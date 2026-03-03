@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-layer-group"></i>
                        Importación Batch de Noticias
                    </h3>
                </div>
                <div class="card-body">
                    <form id="batchImportForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="source_name">Nombre de la Fuente</label>
                                    <input type="text" class="form-control" id="source_name" name="source_name" 
                                           value="Chat AI Batch" required>
                                    <small class="form-text text-muted">Nombre de la fuente original (ej: Twitter, Gemini, etc.)</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <button type="button" class="btn btn-info" onclick="showFormatHelp()">
                                        <i class="fas fa-question-circle"></i> Ver Formato
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="batch_content">Contenido Batch</label>
                            <textarea class="form-control" id="batch_content" name="batch_content" 
                                      rows="15" required placeholder="🚨 Título de la noticia
URL: https://ejemplo.com/noticia
Contenido: Contenido completo de la noticia...
Fuente: Nombre del medio

🚨 Otra noticia
URL: https://ejemplo.com/otra
Contenido: Otro contenido...
Fuente: Otro medio"></textarea>
                            <small class="form-text text-muted">Pega aquí las noticias en el formato especificado</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-rocket"></i> Procesar Noticias
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="clearForm()">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </form>

                    <!-- Preview Section -->
                    <div id="previewSection" style="display: none;" class="mt-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-eye"></i> Vista Previa</h5>
                            </div>
                            <div class="card-body">
                                <div id="previewContent"></div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-success" onclick="confirmImport()">
                                        <i class="fas fa-check"></i> Confirmar Importación
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="editContent()">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Section -->
                    <div id="resultsSection" style="display: none;" class="mt-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-line"></i> Resultados del Procesamiento</h5>
                            </div>
                            <div class="card-body">
                                <div id="resultsContent"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Format Help Modal -->
<div class="modal fade" id="formatHelpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Formato de Importación Batch</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6>Formato Requerido:</h6>
                <pre class="bg-light p-3 rounded">🚨 Título de la noticia
URL: https://ejemplo.com/noticia
Contenido: Contenido completo de la noticia...
Fuente: Nombre del medio

🚨 Otra noticia
URL: https://ejemplo.com/otra
Contenido: Otro contenido...
Fuente: Otro medio</pre>

                <h6>Reglas:</h6>
                <ul>
                    <li>Cada noticia debe comenzar con 🚨</li>
                    <li>URL es opcional (se generará una si no existe)</li>
                    <li>Contenido debe ser sustancial (mínimo 50 caracteres)</li>
                    <li>Fente es opcional (usará el nombre por defecto)</li>
                    <li>Separar cada noticia con una línea en blanco</li>
                </ul>

                <h6>Ejemplo Real:</h6>
                <pre class="bg-light p-3 rounded">🚨 Incautación de armas en Angol
URL: https://www.ejemplo.cl/noticia
Contenido: La PDI realizó un operativo nocturno en Angol donde incautaron armas artesanales y droga. El procedimiento contó con apoyo técnico y judicial.
Fuente: Diario Local</pre>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#batchImportForm').on('submit', function(e) {
        e.preventDefault();
        processBatch();
    });
});

function showFormatHelp() {
    $('#formatHelpModal').modal('show');
}

function clearForm() {
    $('#batchImportForm')[0].reset();
    $('#previewSection, #resultsSection').hide();
}

function processBatch() {
    const formData = {
        _token: $('input[name="_token"]').val(),
        batch_content: $('#batch_content').val(),
        source_name: $('#source_name').val()
    };

    if (!formData.batch_content.trim()) {
        alert('Por favor ingresa el contenido batch');
        return;
    }

    // Mostrar loading
    const submitBtn = $('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Analizando...').prop('disabled', true);

    $.ajax({
        url: '{{ route("admin.gemini.batch.process") }}',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                showPreview(response);
            } else {
                alert('Error: ' + (response.error || 'Error desconocido'));
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Error de conexión';
            alert('Error: ' + error);
        },
        complete: function() {
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
}

function showPreview(response) {
    const previewHtml = `
        <div class="alert alert-info">
            <strong>Se detectaron ${response.articles_detected} noticias:</strong>
        </div>
        <div class="list-group">
            ${response.preview.map((article, index) => `
                <div class="list-group-item">
                    <h6>🚨 ${article.title}</h6>
                    <small class="text-muted">
                        Fuente: ${article.source} | 
                        Caracteres: ${article.content_length} | 
                        URL: ${article.has_url ? '✅' : '❌'}
                    </small>
                </div>
            `).join('')}
        </div>
    `;

    $('#previewContent').html(previewHtml);
    $('#previewSection').show();
    
    // Store response for confirmation
    window.currentBatchResponse = response;
}

function confirmImport() {
    if (!window.currentBatchResponse) return;

    const resultsHtml = `
        <div class="alert alert-success">
            <h5><i class="fas fa-check-circle"></i> Importación Exitosa</h5>
            <p>${window.currentBatchResponse.message}</p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3>${window.currentBatchResponse.articles_detected}</h3>
                        <p>Noticias Detectadas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3>${window.currentBatchResponse.articles_processed}</h3>
                        <p>Enviadas a Procesamiento</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3>Cola Gemini</h3>
                        <p>Procesando con IA</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('admin.gemini.stats') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Ver Estadísticas
            </a>
            <button type="button" class="btn btn-primary" onclick="clearForm()">
                <i class="fas fa-plus"></i> Nueva Importación
            </button>
        </div>
    `;

    $('#resultsContent').html(resultsHtml);
    $('#resultsSection').show();
    $('#previewSection').hide();
}

function editContent() {
    $('#previewSection').hide();
    $('#batch_content').focus();
}
</script>
@endpush
