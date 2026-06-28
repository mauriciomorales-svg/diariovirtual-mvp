<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeminiService
{
    private $apiKey;
    private $model;
    private $systemInstruction;

    /** Orden: primero 1.5 (suele ir mejor en nivel gratuito), luego 2.x */
    private array $availableModels = [
        'gemini-1.5-flash',
        'gemini-1.5-flash-latest',
        'gemini-1.5-flash-8b',
        'gemini-2.0-flash',
        'gemini-2.0-flash-001',
        'gemini-2.0-flash-lite',
        'gemini-2.5-flash',
        'gemini-2.5-pro',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-2.0-flash-001');
        
        // Instrucciones estrictas para transformación legal y segura
        $this->systemInstruction = <<<EOT
Eres el Editor Jefe de Diario Malleco, un medio digital de la Provincia de Malleco, Araucanía, Chile.

TU MISIÓN: Transformar COMPLETAMENTE noticias de fuentes externas en contenido ORIGINAL para nuestros lectores de Angol, Victoria, Collipulli y comunas cercanas.

REGLAS ESTRICTAS DE TRANSFORMACIÓN:
1. Título COMPLETAMENTE diferente al original, con emoji 🚨 al inicio
2. NO copies frases largas del texto original - reescribe TODO con tus propias palabras
3. Estructura: Mínimo 3 párrafos cortos, máximo 5
4. Inyectar OBLIGATORIAMENTE el tag [NATIVE_AD_PLACEHOLDER] después del segundo párrafo
5. Enfoque 100% local - conecta la noticia con la realidad de Malleco
6. Agregar al FINAL del contenido: "Fuente: [NOMBRE_FUENTE] | Leer noticia original"
7. Respuesta OBLIGATORIA en JSON estructurado

REGLAS LEGALES:
- Esto es AGREGACIÓN DE NOTICIAS, no plagio
- Cita siempre la fuente original
- Transforma el 100% del contenido
- No uses comillas extensas del original

AUDIENCIA: Vecinos de la Provincia de Malleco, gente común que necesita información relevante.
EOT;
    }

    /**
     * Obtiene el modelo configurado o detecta uno disponible
     */
    public function getWorkingModel(): string
    {
        // Verificar si hay un modelo en caché que funcione
        $cachedModel = Cache::get('gemini_working_model');
        if ($cachedModel) {
            return $cachedModel;
        }

        // Probar el modelo configurado primero (tope para no superar max_execution_time del servidor)
        $modelsToTest = array_unique(array_merge([$this->model], $this->availableModels));
        $maxAttempts = 5;

        foreach ($modelsToTest as $model) {
            if ($maxAttempts-- <= 0) {
                break;
            }
            if ($this->testModel($model)) {
                Cache::put('gemini_working_model', $model, 3600); // Cache por 1 hora
                Log::info('Gemini working model detected', ['model' => $model]);
                return $model;
            }
        }

        // Si ninguno funciona, devolver el configurado por defecto
        Log::warning('No working Gemini model found, using default');
        return $this->model;
    }

    /**
     * Prueba si un modelo está disponible
     */
    private function testModel(string $model): bool
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [['parts' => [['text' => 'Hello']]]],
                    'generationConfig' => ['maxOutputTokens' => 10]
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::debug("Model {$model} test failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Lista todos los modelos disponibles en la API
     */
    public function listAvailableModels(): array
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models?key={$this->apiKey}";
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $models = [];

                foreach ($data['models'] ?? [] as $model) {
                    $models[] = [
                        'name' => str_replace('models/', '', $model['name']),
                        'displayName' => $model['displayName'] ?? '',
                        'description' => $model['description'] ?? '',
                        'supportedGenerationMethods' => $model['supportedGenerationMethods'] ?? [],
                    ];
                }

                return $models;
            }
        } catch (\Exception $e) {
            Log::error('Failed to list Gemini models', ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Transforma una noticia usando Gemini AI con atribución de fuente
     */
    public function transformArticle(string $originalContent, string $originalTitle, string $sourceName = '', string $originalUrl = ''): array
    {
        try {
            $cacheKey = 'gemini_transform_' . md5($originalContent . $originalTitle . $sourceName);
            $cached = Cache::get($cacheKey);

            if ($cached) {
                Log::info('Gemini transformation from cache', ['cache_key' => $cacheKey]);
                return $cached;
            }

            $model = $this->getWorkingModel();
            $prompt = $this->buildPrompt($originalContent, $originalTitle, $sourceName, $originalUrl);
            $response = $this->callGeminiAPI($prompt, $model);

            if (!$response['success']) {
                throw new \Exception('Gemini API call failed: ' . $response['error']);
            }

            $transformed = $this->processResponse($response['data'], $sourceName, $originalUrl);
            $transformed['metadata']['model_used'] = $model;
            $transformed['metadata']['original_source'] = $sourceName;
            $transformed['metadata']['original_url'] = $originalUrl;

            Cache::put($cacheKey, $transformed, 3600);

            Log::info('Gemini transformation completed', [
                'original_title' => $originalTitle,
                'transformed_title' => $transformed['title'],
                'source' => $sourceName,
                'model' => $model,
                'word_count' => str_word_count($transformed['content'])
            ]);

            return $transformed;
        } catch (\Exception $e) {
            Log::error('Gemini transformation failed', [
                'error' => $e->getMessage(),
                'original_title' => $originalTitle,
                'source' => $sourceName
            ]);
            throw $e;
        }
    }

    /**
     * Construye el prompt para Gemini con información de fuente
     */
    private function buildPrompt(string $content, string $title, string $sourceName = '', string $originalUrl = ''): string
    {
        $sourceInfo = $sourceName ? "\nFuente original: {$sourceName}\nURL original: {$originalUrl}" : '';
        
        return <<<EOT
=== NOTICIA A TRANSFORMAR ===

TÍTULO ORIGINAL (solo referencia, NO usar):
{$title}

CONTENIDO ORIGINAL (reescribir COMPLETAMENTE):
{$content}
{$sourceInfo}

=== INSTRUCCIONES OBLIGATORIAS ===
{$this->systemInstruction}

=== FORMATO JSON REQUERIDO ===
Responde ÚNICAMENTE con JSON válido en este formato exacto:

{
  "success": true,
  "data": {
    "title": "🚨 NUEVO TÍTULO COMPLETAMENTE DIFERENTE (máx 80 chars)",
    "slug": "slug-nuevo-diferente-al-original",
    "excerpt": "Resumen único de 255 caracteres en palabras propias",
    "content": "Párrafo 1 corto en 2-3 oraciones reescrito.\n\nPárrafo 2 con contexto local Malleco.\n\n[NATIVE_AD_PLACEHOLDER]\n\nPárrafo 3 con cierre e implicancias.\n\n---\n\n📰 **Agregador de Noticias - Provincia de Malleco**\\n\\n📝 Fuente: {$sourceName} | 🔗 [Leer noticia original]({$originalUrl})",
    "image_url": "https://via.placeholder.com/1200x630/1a365d/ffffff?text=Noticia+Malleco",
    "metadata": {
      "original_source": "{$sourceName}",
      "original_url": "{$originalUrl}",
      "local_focus": "provincia-malleco",
      "urgency_level": "medium",
      "word_count": 245,
      "transformation_level": "complete"
    }
  }
}

IMPORTANTE: 
- El título debe ser COMPLETAMENTE diferente al original
- NO uses frases extensas del texto original
- Cita la fuente al final
- El slug debe ser único y descriptivo
EOT;
    }

    /**
     * POST a generateContent con reintentos si Google devuelve 429 (cuota / RPM).
     */
    private function executeGeminiHttpPost(string $url, array $payload): Response
    {
        $maxAttempts = 6;
        $response = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $response = Http::timeout(90)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->status() !== 429) {
                return $response;
            }

            if ($attempt >= $maxAttempts) {
                return $response;
            }

            $wait = $this->parseGemini429RetrySeconds($response);
            if ($wait === null) {
                $wait = min(90, 5 * $attempt);
            }

            Log::notice('Gemini 429, reintento tras espera', [
                'attempt' => $attempt,
                'wait_seconds' => $wait,
            ]);
            sleep($wait);
        }

        return $response;
    }

    /**
     * Extrae segundos de espera recomendados (cabecera Retry-After o cuerpo JSON de Google).
     */
    private function parseGemini429RetrySeconds(Response $response): ?int
    {
        $ra = $response->header('Retry-After');
        if ($ra !== null && $ra !== '' && is_numeric($ra)) {
            return min(120, max(1, (int) $ra));
        }

        $json = $response->json();
        if (! is_array($json)) {
            return null;
        }

        foreach ($json['error']['details'] ?? [] as $d) {
            if (! is_array($d)) {
                continue;
            }
            $type = $d['@type'] ?? '';
            if (str_contains((string) $type, 'RetryInfo')) {
                $rd = $d['retryDelay'] ?? '';
                if (is_string($rd) && preg_match('/(\d+)/', $rd, $m)) {
                    return min(120, max(1, (int) $m[1]));
                }
            }
        }

        $msg = (string) ($json['error']['message'] ?? '');
        if (preg_match('/retry in (?:approximately )?(\d+)\s*seconds?/i', $msg, $m)) {
            return min(120, max(1, (int) $m[1]));
        }
        if (preg_match('/(\d+)\s*seconds?/i', $msg, $m)) {
            return min(120, max(1, (int) $m[1]));
        }

        return null;
    }

    /**
     * Llama a la API de Gemini
     */
    private function callGeminiAPI(string $prompt, string $model): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 8192,
            ]
        ];

        $startTime = microtime(true);

        $response = $this->executeGeminiHttpPost($url, $payload);

        $processingTime = (microtime(true) - $startTime) * 1000;

        if (!$response->successful()) {
            return [
                'success' => false,
                'error' => 'HTTP ' . $response->status() . ': ' . $response->body(),
                'processing_time' => $processingTime
            ];
        }

        return [
            'success' => true,
            'data' => $response->json(),
            'processing_time' => $processingTime
        ];
    }

    /**
     * Decodifica JSON que Gemini devuelve a veces con saltos de línea u otros controles sin escapar dentro de strings.
     */
    private function parseJsonFromGeminiText(string $raw): array
    {
        $text = trim($raw);
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
        $text = preg_replace('/^```json\s*/iu', '', $text);
        $text = preg_replace('/^```\s*/u', '', $text);
        $text = preg_replace('/\s*```$/u', '', $text);
        $text = trim($text);

        if ($text === '') {
            throw new \Exception('Gemini devolvió texto vacío en lugar de JSON.');
        }

        $flags = JSON_INVALID_UTF8_SUBSTITUTE | JSON_BIGINT_AS_STRING;

        $toTry = [$text];
        if (preg_match('/\{[\s\S]*\}/s', $text, $m)) {
            $candidate = $m[0];
            if ($candidate !== $text) {
                $toTry[] = $candidate;
            }
            $fixed = $this->escapeUnescapedControlCharsInJsonStrings($candidate);
            if ($fixed !== $candidate) {
                $toTry[] = $fixed;
            }
        }

        $toTry = array_values(array_unique($toTry));

        $lastSyntaxError = null;
        foreach ($toTry as $chunk) {
            $data = json_decode($chunk, true, 512, $flags);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $lastSyntaxError = json_last_error_msg();

                continue;
            }
            if (! is_array($data)) {
                throw new \Exception(
                    'Gemini devolvió JSON válido pero no un objeto { } (tipo: '.gettype($data).'). Suele ser respuesta bloqueada, truncada o texto fuera del formato.'
                );
            }

            return $data;
        }

        Log::warning('Gemini JSON decode failed', [
            'json_error' => $lastSyntaxError,
            'snippet' => mb_substr($text, 0, 500),
        ]);

        throw new \Exception(
            'Invalid JSON from Gemini: '.($lastSyntaxError ?? 'no se pudo decodificar')
        );
    }

    /**
     * Convierte caracteres de control ASCII (excepto tab) dentro de comillas en escapes \u00xx válidos para JSON.
     */
    private function escapeUnescapedControlCharsInJsonStrings(string $json): string
    {
        $out = '';
        $n = strlen($json);
        $inString = false;
        $escape = false;

        for ($i = 0; $i < $n; $i++) {
            $c = $json[$i];

            if ($escape) {
                $out .= $c;
                $escape = false;

                continue;
            }

            if ($inString && $c === '\\') {
                $out .= $c;
                $escape = true;

                continue;
            }

            if ($c === '"') {
                $inString = ! $inString;
                $out .= $c;

                continue;
            }

            if ($inString) {
                $ord = ord($c);
                if ($ord < 32 && $ord !== 9) {
                    $out .= sprintf('\\u%04x', $ord);

                    continue;
                }
            }

            $out .= $c;
        }

        return $out;
    }

    /**
     * Procesa y valida la respuesta de Gemini
     */
    private function processResponse(array $response, string $sourceName = '', string $originalUrl = ''): array
    {
        if (! empty($response['promptFeedback']['blockReason'])) {
            throw new \Exception(
                'Gemini bloqueó el prompt: '.$response['promptFeedback']['blockReason']
            );
        }

        $candidates = $response['candidates'] ?? [];
        if ($candidates === []) {
            throw new \Exception('Gemini no devolvió candidatos (bloqueo, cuota o error del modelo).');
        }

        $first = $candidates[0];
        $finishReason = (string) ($first['finishReason'] ?? '');
        if ($finishReason === 'MAX_TOKENS') {
            throw new \Exception(
                'Gemini cortó la respuesta por límite de tokens (JSON incompleto). Acorta el texto de entrada o reintenta.'
            );
        }
        if ($finishReason !== '' && $finishReason !== 'STOP' && preg_match('/SAFETY|RECITATION|BLOCKLIST|PROHIBITED_CONTENT|SPII|LANGUAGE/i', $finishReason)) {
            throw new \Exception('Gemini detuvo la generación: '.$finishReason);
        }

        $generatedText = $first['content']['parts'][0]['text'] ?? '';

        if ($generatedText === '' || $generatedText === null) {
            throw new \Exception('Empty response from Gemini (sin texto en candidates). finishReason: '.($finishReason ?: 'desconocido'));
        }

        $parsed = $this->parseJsonFromGeminiText($generatedText);

        if (!isset($parsed['success']) || !$parsed['success']) {
            throw new \Exception('Gemini reported failure');
        }

        if (!isset($parsed['data'])) {
            throw new \Exception('Missing data in Gemini response');
        }

        $data = $parsed['data'];

        // Validar campos requeridos
        $required = ['title', 'slug', 'excerpt', 'content'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // Validar que el título tenga emoji 🚨
        if (!str_contains($data['title'], '🚨')) {
            $data['title'] = '🚨 ' . $data['title'];
        }

        // Validar que el contenido tenga el placeholder
        if (!str_contains($data['content'], '[NATIVE_AD_PLACEHOLDER]')) {
            throw new \Exception('Missing [NATIVE_AD_PLACEHOLDER] in content');
        }

        // Generar slug si no existe
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug(str_replace('🚨 ', '', $data['title']));
        }

        // Validar límite de excerpt
        if (strlen($data['excerpt']) > 255) {
            $data['excerpt'] = substr($data['excerpt'], 0, 252) . '...';
        }

        // Asegurar que haya atribución de fuente en el contenido
        if ($sourceName && !str_contains($data['content'], 'Fuente:')) {
            $data['content'] .= "\n\n---\n\n📰 **Agregador de Noticias - Provincia de Malleco**\n\n📝 Fuente: {$sourceName} | 🔗 Leer noticia original";
        }

        // Agregar metadata de fuente
        $data['metadata']['original_source'] = $sourceName;
        $data['metadata']['original_url'] = $originalUrl;
        $data['metadata']['transformation_level'] = 'complete';

        return $data;
    }

    /**
     * Verifica si el servicio está disponible y tiene cuota.
     * No usa getWorkingModel(): el descubrimiento de modelo hace varias llamadas HTTP en frío
     * y puede superar max_execution_time del servidor → 500 con HTML en lugar de JSON.
     */
    public function healthCheck(): array
    {
        try {
            if (empty($this->apiKey)) {
                return [
                    'available' => false,
                    'error' => 'GEMINI_API_KEY no configurada',
                    'quota_exceeded' => false,
                    'model' => $this->model,
                ];
            }

            // Caché corta; si el driver de caché falla (Redis caído, etc.), probamos sin caché.
            try {
                return Cache::remember('gemini_health_check_snapshot', 45, fn () => $this->pingGeminiOnce());
            } catch (\Throwable $e) {
                Log::warning('Gemini health cache unavailable', ['error' => $e->getMessage()]);

                return $this->pingGeminiOnce();
            }
        } catch (\Throwable $e) {
            Log::error('Gemini health check failed', ['error' => $e->getMessage()]);
            return [
                'available' => false,
                'error' => $e->getMessage(),
                'quota_exceeded' => false,
                'model' => $this->model
            ];
        }
    }

    /**
     * Modelos a probar en health/ping: configurado primero, luego lista (sin duplicados).
     */
    private function candidateModelsForPing(): array
    {
        $merged = array_merge([$this->model], $this->availableModels);

        return array_values(array_unique(array_filter($merged)));
    }

    /**
     * Prioriza el modelo que ya funcionó (caché), luego el resto.
     *
     * @param  array<int, string>  $candidates
     * @return array<int, string>
     */
    private function orderCandidatesWithCache(array $candidates): array
    {
        try {
            $cached = Cache::get('gemini_working_model');
        } catch (\Throwable $e) {
            Log::debug('Gemini health: cache read skipped', ['error' => $e->getMessage()]);

            return $candidates;
        }

        if (! is_string($cached) || $cached === '' || ! in_array($cached, $candidates, true)) {
            return $candidates;
        }

        return array_values(array_merge([$cached], array_values(array_diff($candidates, [$cached]))));
    }

    private function parseGeminiApiErrorMessage(\Illuminate\Http\Client\Response $response): ?string
    {
        $json = $response->json();
        if (is_array($json) && isset($json['error']['message']) && is_string($json['error']['message'])) {
            return $json['error']['message'];
        }

        return null;
    }

    /**
     * Una petición de comprobación por modelo; prueba varios modelos si hay 429 u otro fallo recuperable.
     */
    private function pingGeminiOnce(): array
    {
        try {
            $candidates = $this->orderCandidatesWithCache($this->candidateModelsForPing());
            $lastHttpStatus = null;
            $lastGoogleMessage = null;
            $modelsTried = [];

            foreach ($candidates as $model) {
                $modelsTried[] = $model;
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

                $pending = Http::timeout(12)
                    ->withHeaders(['Content-Type' => 'application/json']);

                if (method_exists($pending, 'connectTimeout')) {
                    $pending = $pending->connectTimeout(5);
                }

                $response = $pending->post($url, [
                    'contents' => [['parts' => [['text' => 'Say "OK"']]]],
                    'generationConfig' => ['maxOutputTokens' => 5],
                ]);

                $lastHttpStatus = $response->status();
                $lastGoogleMessage = $this->parseGeminiApiErrorMessage($response);

                if ($response->successful()) {
                    try {
                        Cache::put('gemini_working_model', $model, 3600);
                    } catch (\Throwable $e) {
                        Log::debug('Gemini health: could not cache working model', ['error' => $e->getMessage()]);
                    }

                    return [
                        'available' => true,
                        'error' => null,
                        'quota_exceeded' => false,
                        'model' => $model,
                        'google_error' => null,
                        'models_tried' => $modelsTried,
                    ];
                }

                if ($response->status() === 429) {
                    Log::notice('Gemini ping 429, trying next model', ['model' => $model, 'google' => $lastGoogleMessage]);

                    continue;
                }

                // Modelo inexistente u otro error: probar siguiente candidato
                if (in_array($response->status(), [400, 404], true)) {
                    Log::notice('Gemini ping model error, trying next', [
                        'model' => $model,
                        'status' => $response->status(),
                        'google' => $lastGoogleMessage,
                    ]);

                    continue;
                }

                return [
                    'available' => false,
                    'error' => $this->formatPingFailureMessage($response->status(), $lastGoogleMessage, false),
                    'quota_exceeded' => false,
                    'model' => $model,
                    'google_error' => $lastGoogleMessage,
                    'models_tried' => $modelsTried,
                ];
            }

            $is429 = ($lastHttpStatus === 429);

            return [
                'available' => false,
                'error' => $this->formatPingFailureMessage($lastHttpStatus ?? 0, $lastGoogleMessage, $is429),
                'quota_exceeded' => $is429,
                'model' => $this->model,
                'google_error' => $lastGoogleMessage,
                'models_tried' => $modelsTried,
            ];
        } catch (\Throwable $e) {
            Log::error('Gemini pingGeminiOnce failed', ['error' => $e->getMessage()]);

            return [
                'available' => false,
                'error' => $e->getMessage(),
                'quota_exceeded' => false,
                'model' => $this->model,
                'google_error' => null,
                'models_tried' => [],
            ];
        }
    }

    private function formatPingFailureMessage(int $status, ?string $googleMessage, bool $rateLimited): string
    {
        $detail = $googleMessage ? ' ' . $googleMessage : '';

        if ($rateLimited || $status === 429) {
            return trim(
                'Límite o cuota de la API (429 Too Many Requests). No implica solo “mucho uso hoy”: puede ser RPM, modelo o proyecto.'
                .$detail
                .' Revisa cuotas en Google Cloud (Generative Language API) y restricciones de la clave.'
            );
        }

        if ($status > 0) {
            return trim('HTTP '.$status.$detail);
        }

        return 'Error desconocido al contactar Gemini.'.$detail;
    }

    /**
     * Transforma una noticia usando Gemini AI con configuración avanzada
     */
    public function transformArticleAdvanced(string $originalContent, string $originalTitle, array $config = [], string $sourceName = '', string $originalUrl = ''): array
    {
        try {
            $cacheKey = 'gemini_transform_advanced_' . md5($originalContent . $originalTitle . json_encode($config) . $sourceName);
            $cached = Cache::get($cacheKey);

            if ($cached) {
                Log::info('Gemini advanced transformation from cache', ['cache_key' => $cacheKey]);
                return $cached;
            }

            $model = $this->getWorkingModel();
            $prompt = $this->buildAdvancedPrompt($originalContent, $originalTitle, $config, $sourceName, $originalUrl);
            $response = $this->callGeminiAPIAdvanced($prompt, $model, $config);

            if (!$response['success']) {
                throw new \Exception('Gemini API call failed: ' . $response['error']);
            }

            $transformed = $this->processResponse($response['data'], $sourceName, $originalUrl);
            $transformed['metadata']['model_used'] = $model;
            $transformed['metadata']['advanced_config'] = $config;

            Cache::put($cacheKey, $transformed, 3600);

            Log::info('Gemini advanced transformation completed', [
                'original_title' => $originalTitle,
                'transformed_title' => $transformed['title'],
                'source' => $sourceName,
                'model' => $model,
                'config' => $config,
                'word_count' => str_word_count($transformed['content'])
            ]);

            return $transformed;
        } catch (\Exception $e) {
            Log::error('Gemini advanced transformation failed', [
                'error' => $e->getMessage(),
                'original_title' => $originalTitle,
                'source' => $sourceName,
                'config' => $config
            ]);
            throw $e;
        }
    }

    /**
     * Construye el prompt avanzado para Gemini
     */
    private function buildAdvancedPrompt(string $content, string $title, array $config, string $sourceName = '', string $originalUrl = ''): string
    {
        $temperature = $config['temperature'] ?? 0.7;
        $maxLength = $config['maxLength'] ?? 'medium';
        $localStyle = $config['localStyle'] ?? 'malleco';
        
        $lengthInstructions = [
            'short' => 'máximo 100 palabras',
            'medium' => 'aproximadamente 200 palabras',
            'long' => 'más de 300 palabras'
        ];
        
        $styleInstructions = [
            'malleco' => 'enfocado en toda la provincia de Malleco',
            'angol' => 'enfocado específicamente en Angol',
            'victoria' => 'enfocado específicamente en Victoria',
            'collipulli' => 'enfocado específicamente en Collipulli'
        ];

        $sourceInfo = $sourceName ? "\nFuente: {$sourceName}\nURL: {$originalUrl}" : '';
        $basePrompt = "Título original: {$title}\n\nContenido original:\n{$content}{$sourceInfo}\n\n";
        
        $advancedPrompt = "Usando las siguientes instrucciones avanzadas:\n";
        $advancedPrompt .= "1. Eres el Editor Jefe de Diario Malleco\n";
        $advancedPrompt .= "2. Temperatura: {$temperature} (creatividad vs precisión)\n";
        $advancedPrompt .= "3. Longitud: {$lengthInstructions[$maxLength]}\n";
        $advancedPrompt .= "4. Estilo local: {$styleInstructions[$localStyle]}\n";
        $advancedPrompt .= "5. Titular COMPLETAMENTE diferente, con emoji 🚨\n";
        $advancedPrompt .= "6. NO copies frases del original - reescribe TODO\n";
        $advancedPrompt .= "7. Párrafos cortos y claros\n";
        $advancedPrompt .= "8. Enfoque 100% local\n";
        $advancedPrompt .= "9. Inyectar [NATIVE_AD_PLACEHOLDER] después del segundo párrafo\n";
        $advancedPrompt .= "10. Agregar al final: Fuente: {$sourceName} | Leer noticia original\n";
        $advancedPrompt .= "11. Respuesta obligatoria en JSON estructurado\n\n";
        
        $advancedPrompt .= "Responde ÚNICAMENTE con JSON en este formato exacto:\n";
        $advancedPrompt .= "{\n  \"success\": true,\n  \"data\": {\n    \"title\": \"🚨 Título transformado\",\n    \"slug\": \"slug-transformado\",\n    \"excerpt\": \"Resumen de 255 caracteres\",\n    \"content\": \"Párrafo 1 corto.\\n\\nPárrafo 2 con contexto local.\\n\\n[NATIVE_AD_PLACEHOLDER]\\n\\nPárrafo 3 con acciones.\\n\\n---\\n\\n📰 Agregador Noticias Malleco\\n\\n📝 Fuente: {$sourceName} | 🔗 Leer original\",\n    \"image_url\": \"https://via.placeholder.com/1200x630/1a365d/ffffff?text=Noticia+Malleco\",\n    \"metadata\": {\n      \"original_source\": \"{$sourceName}\",\n      \"original_url\": \"{$originalUrl}\",\n      \"local_focus\": \"comunidad-local\",\n      \"urgency_level\": \"high|medium|low\",\n      \"word_count\": 245,\n      \"temperature_used\": {$temperature},\n      \"length_target\": \"{$maxLength}\",\n      \"style_focus\": \"{$localStyle}\",\n      \"transformation_level\": \"complete\"\n    }\n  }\n}";

        return $basePrompt . $advancedPrompt;
    }

    /**
     * Llama a la API de Gemini con configuración avanzada
     */
    private function callGeminiAPIAdvanced(string $prompt, string $model, array $config): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => $config['temperature'] ?? 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 8192,
            ]
        ];

        $startTime = microtime(true);

        $response = $this->executeGeminiHttpPost($url, $payload);

        $processingTime = (microtime(true) - $startTime) * 1000;

        if (!$response->successful()) {
            return [
                'success' => false,
                'error' => 'HTTP ' . $response->status() . ': ' . $response->body(),
                'processing_time' => $processingTime
            ];
        }

        return [
            'success' => true,
            'data' => $response->json(),
            'processing_time' => $processingTime
        ];
    }

    /**
     * Obtiene información de diagnóstico
     */
    public function getDiagnostics(): array
    {
        $health = $this->healthCheck();
        
        return [
            'configured_model' => $this->model,
            'working_model' => $health['model'],
            'available_models' => $this->listAvailableModels(),
            'api_key_configured' => !empty($this->apiKey),
            'health_check' => $health,
            'service_available' => $health['available'],
            'quota_exceeded' => $health['quota_exceeded'] ?? false,
        ];
    }
}
