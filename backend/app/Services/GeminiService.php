<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeminiService
{
    private $apiKey;
    private $model;
    private $systemInstruction;

    // Modelos disponibles en la API key actual (gemini-2.x)
    private $availableModels = [
        'gemini-2.0-flash-001',
        'gemini-2.0-flash',
        'gemini-2.0-flash-lite',
        'gemini-2.5-flash',
        'gemini-2.5-pro',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-2.0-flash-001');
        $this->systemInstruction = "Eres el Editor Jefe de Diario Malleco. Tu tarea es reescribir noticias nacionales para una audiencia de la Provincia de Malleco (Angol, Victoria, Collipulli, etc.). Reglas: 1. Titular con emoji 🚨. 2. Párrafos cortos. 3. Enfoque 100% local. 4. Inyectar el tag [NATIVE_AD_PLACEHOLDER] después del segundo párrafo. 5. Respuesta obligatoria en JSON estructurado.";
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

        // Probar el modelo configurado primero
        $modelsToTest = array_unique(array_merge([$this->model], $this->availableModels));

        foreach ($modelsToTest as $model) {
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
        } catch (\Exception $e) {
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
     * Transforma una noticia usando Gemini AI
     */
    public function transformArticle(string $originalContent, string $originalTitle): array
    {
        try {
            $cacheKey = 'gemini_transform_' . md5($originalContent . $originalTitle);
            $cached = Cache::get($cacheKey);

            if ($cached) {
                Log::info('Gemini transformation from cache', ['cache_key' => $cacheKey]);
                return $cached;
            }

            $model = $this->getWorkingModel();
            $prompt = $this->buildPrompt($originalContent, $originalTitle);
            $response = $this->callGeminiAPI($prompt, $model);

            if (!$response['success']) {
                throw new \Exception('Gemini API call failed: ' . $response['error']);
            }

            $transformed = $this->processResponse($response['data']);
            $transformed['metadata']['model_used'] = $model;

            Cache::put($cacheKey, $transformed, 3600);

            Log::info('Gemini transformation completed', [
                'original_title' => $originalTitle,
                'transformed_title' => $transformed['title'],
                'model' => $model,
                'word_count' => str_word_count($transformed['content'])
            ]);

            return $transformed;
        } catch (\Exception $e) {
            Log::error('Gemini transformation failed', [
                'error' => $e->getMessage(),
                'original_title' => $originalTitle
            ]);
            throw $e;
        }
    }

    /**
     * Construye el prompt para Gemini
     */
    private function buildPrompt(string $content, string $title): string
    {
        return "Título original: {$title}\n\nContenido original:\n{$content}\n\nUsando las siguientes instrucciones:\n{$this->systemInstruction}\n\nResponde ÚNICAMENTE con JSON en este formato exacto:\n{\n  \"success\": true,\n  \"data\": {\n    \"title\": \"🚨 Título transformado\",\n    \"slug\": \"slug-transformado\",\n    \"excerpt\": \"Resumen de 255 caracteres\",\n    \"content\": \"Párrafo 1 corto.\\n\\nPárrafo 2 con contexto local.\\n\\n[NATIVE_AD_PLACEHOLDER]\\n\\nPárrafo 3 con acciones recomendadas.\",\n    \"image_url\": \"https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco\",\n    \"metadata\": {\n      \"original_source\": \"fuente-original\",\n      \"local_focus\": \"comunidad-local\",\n      \"urgency_level\": \"high|medium|low\",\n      \"word_count\": 245\n    }\n  }\n}";
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
                'maxOutputTokens' => 2048,
            ]
        ];

        $startTime = microtime(true);

        $response = Http::timeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

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
     * Procesa y valida la respuesta de Gemini
     */
    private function processResponse(array $response): array
    {
        $generatedText = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($generatedText)) {
            throw new \Exception('Empty response from Gemini');
        }

        // Limpiar posible markdown de código
        $generatedText = preg_replace('/^```json\s*/', '', $generatedText);
        $generatedText = preg_replace('/\s*```$/', '', $generatedText);

        $parsed = json_decode($generatedText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON from Gemini: ' . json_last_error_msg());
        }

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

        return $data;
    }

    /**
     * Verifica si el servicio está disponible y tiene cuota
     */
    public function healthCheck(): array
    {
        try {
            $model = $this->getWorkingModel();
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [['parts' => [['text' => 'Say "OK"']]]],
                    'generationConfig' => ['maxOutputTokens' => 5]
                ]);

            if ($response->status() === 429) {
                return [
                    'available' => false,
                    'error' => 'Cuota agotada (429)',
                    'quota_exceeded' => true,
                    'model' => $model
                ];
            }

            if (!$response->successful()) {
                return [
                    'available' => false,
                    'error' => 'HTTP ' . $response->status(),
                    'quota_exceeded' => false,
                    'model' => $model
                ];
            }

            return [
                'available' => true,
                'error' => null,
                'quota_exceeded' => false,
                'model' => $model
            ];
        } catch (\Exception $e) {
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
     * Transforma una noticia usando Gemini AI con configuración avanzada
     */
    public function transformArticleAdvanced(string $originalContent, string $originalTitle, array $config = []): array
    {
        try {
            $cacheKey = 'gemini_transform_advanced_' . md5($originalContent . $originalTitle . json_encode($config));
            $cached = Cache::get($cacheKey);

            if ($cached) {
                Log::info('Gemini advanced transformation from cache', ['cache_key' => $cacheKey]);
                return $cached;
            }

            $model = $this->getWorkingModel();
            $prompt = $this->buildAdvancedPrompt($originalContent, $originalTitle, $config);
            $response = $this->callGeminiAPIAdvanced($prompt, $model, $config);

            if (!$response['success']) {
                throw new \Exception('Gemini API call failed: ' . $response['error']);
            }

            $transformed = $this->processResponse($response['data']);
            $transformed['metadata']['model_used'] = $model;
            $transformed['metadata']['advanced_config'] = $config;

            Cache::put($cacheKey, $transformed, 3600);

            Log::info('Gemini advanced transformation completed', [
                'original_title' => $originalTitle,
                'transformed_title' => $transformed['title'],
                'model' => $model,
                'config' => $config,
                'word_count' => str_word_count($transformed['content'])
            ]);

            return $transformed;
        } catch (\Exception $e) {
            Log::error('Gemini advanced transformation failed', [
                'error' => $e->getMessage(),
                'original_title' => $originalTitle,
                'config' => $config
            ]);
            throw $e;
        }
    }

    /**
     * Construye el prompt avanzado para Gemini
     */
    private function buildAdvancedPrompt(string $content, string $title, array $config): string
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

        $basePrompt = "Título original: {$title}\n\nContenido original:\n{$content}\n\n";
        
        $advancedPrompt = "Usando las siguientes instrucciones avanzadas:\n";
        $advancedPrompt .= "1. Eres el Editor Jefe de Diario Malleco\n";
        $advancedPrompt .= "2. Temperatura: {$temperature} (creatividad vs precisión)\n";
        $advancedPrompt .= "3. Longitud: {$lengthInstructions[$maxLength]}\n";
        $advancedPrompt .= "4. Estilo local: {$styleInstructions[$localStyle]}\n";
        $advancedPrompt .= "5. Titular con emoji 🚨\n";
        $advancedPrompt .= "6. Párrafos cortos y claros\n";
        $advancedPrompt .= "7. Enfoque 100% local\n";
        $advancedPrompt .= "8. Inyectar [NATIVE_AD_PLACEHOLDER] después del segundo párrafo\n";
        $advancedPrompt .= "9. Respuesta obligatoria en JSON estructurado\n\n";
        
        $advancedPrompt .= "Responde ÚNICAMENTE con JSON en este formato exacto:\n";
        $advancedPrompt .= "{\n  \"success\": true,\n  \"data\": {\n    \"title\": \"🚨 Título transformado\",\n    \"slug\": \"slug-transformado\",\n    \"excerpt\": \"Resumen de 255 caracteres\",\n    \"content\": \"Párrafo 1 corto.\\n\\nPárrafo 2 con contexto local.\\n\\n[NATIVE_AD_PLACEHOLDER]\\n\\nPárrafo 3 con acciones recomendadas.\",\n    \"image_url\": \"https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco\",\n    \"metadata\": {\n      \"original_source\": \"fuente-original\",\n      \"local_focus\": \"comunidad-local\",\n      \"urgency_level\": \"high|medium|low\",\n      \"word_count\": 245,\n      \"temperature_used\": {$temperature},\n      \"length_target\": \"{$maxLength}\",\n      \"style_focus\": \"{$localStyle}\"\n    }\n  }\n}";

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
                'maxOutputTokens' => 2048,
            ]
        ];

        $startTime = microtime(true);

        $response = Http::timeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

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
