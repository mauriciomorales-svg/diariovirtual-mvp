# ESPECIFICACIÓN TÉCNICA DE ALTO NIVEL: PROYECTO "DIARIO VIRTUAL"

**Para**: Senior Fullstack Team (Backend & Frontend)  
**De**: Dirección de Proyecto  
**Fecha**: 1 de Marzo, 2026  
**Prioridad**: Crítica / MVP Operacional

---

## 1. Misión del Sistema

Desarrollar una plataforma de Ingeniería de Tráfico optimizada para la Provincia de Malleco. El sistema debe funcionar como un "Caballo de Troya": un portal de noticias de carga ultra-rápida que captura tráfico viral (WhatsApp/FB) y lo convierte en leads para unidades de negocio B2B/B2C mediante Native Ads inyectados.

---

## 2. Stack de Arquitectura Definitivo

- **Backend**: Laravel 12 (Headless API) + PostgreSQL 16 + Redis (Tags Caching)
- **Frontend**: Next.js 14+ (App Router, Server Components) + Tailwind CSS + TypeScript
- **Infraestructura**: VPS DigitalOcean (Laravel/Postgres) + Vercel Edge Network (Next.js)

---

## 3. Directrices Técnicas de Implementación (Refinadas)

### A. Backend & Data (Laravel)

#### Modelo de Datos Robusto
```php
// Implementar source_hash (unique index) en la tabla articles
Schema::create('articles', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('title');
    $table->string('slug')->unique();
    $table->string('source_hash')->unique(); // sha256/md5 de URL original
    $table->string('excerpt', 255);
    $table->text('content')->nullable();
    $table->string('image_url');
    $table->boolean('is_external')->default(false);
    $table->string('external_url')->nullable();
    $table->enum('status', ['draft', 'scheduled', 'published'])->default('draft');
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
    
    $table->index(['status', 'published_at']);
    $table->index('source_hash');
});
```

#### Scraper RSS (Cron Job)
```php
class ScrapeNewsCommand extends Command
{
    protected $signature = 'news:scrape';
    
    public function handle()
    {
        $feeds = [
            'https://www.biobiochile.cl/rss/bbcl.xml',
            'https://www.malleco7.cl/feed/',
            'https://www.emol.com/rss/todas.xml'
        ];
        
        foreach ($feeds as $feedUrl) {
            $this->processFeed($feedUrl);
        }
    }
    
    private function processFeed($feedUrl)
    {
        $response = Http::timeout(30)->get($feedUrl);
        $xml = simplexml_load_string($response->body());
        
        foreach ($xml->channel->item as $item) {
            $sourceHash = hash('sha256', (string) $item->link);
            
            Article::firstOrCreate(
                ['source_hash' => $sourceHash],
                [
                    'title' => (string) $item->title,
                    'slug' => Str::slug((string) $item->title),
                    'excerpt' => Str::limit(strip_tags((string) $item->description), 255),
                    'image_url' => $this->processImage((string) $item->description),
                    'external_url' => (string) $item->link,
                    'is_external' => true,
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );
        }
    }
    
    private function processImage($content)
    {
        // Image Proxy: No servir URLs externas directamente
        if (preg_match('/<img[^>]+src="([^"]+)"[^>]*>/i', $content, $matches)) {
            $imageUrl = $matches[1];
            return $this->proxyAndOptimizeImage($imageUrl);
        }
        return 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Virtual';
    }
    
    private function proxyAndOptimizeImage($url)
    {
        // Descargar, redimensionar a 1200x630px, cachear localmente
        // Implementar con Intervention Image o similar
        return $url; // Temporal
    }
}
```

#### Caché de Capa 2 (Redis)
```php
class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'articles.index.' . json_encode($request->all());
        
        $articles = Cache::tags(['articles'])->remember($cacheKey, 3600, function () {
            return Article::published()
                ->orderBy('published_at', 'desc')
                ->paginate(20);
        });

        return response()->json($articles);
    }
    
    public function show($slug)
    {
        $cacheKey = "articles.show.{$slug}";
        
        $article = Cache::tags(['articles'])->remember($cacheKey, 3600, function () use ($slug) {
            return Article::where('slug', $slug)
                ->published()
                ->firstOrFail();
        });

        return response()->json($article);
    }
}

// Invalidación automática en eventos de Eloquent
class Article extends Model
{
    protected static function booted()
    {
        static::created(function ($article) {
            Cache::tags(['articles'])->flush();
        });
        
        static::updated(function ($article) {
            Cache::tags(['articles'])->flush();
        });
        
        static::deleted(function ($article) {
            Cache::tags(['articles'])->flush();
        });
    }
}
```

### B. Frontend & UX (Next.js)

#### Estrategia de Renderizado (ISR)
```typescript
// app/page.tsx - Homepage
export const revalidate = 60; // 60 segundos

export default async function HomePage() {
  const articles = await getArticles();
  
  return (
    <div className="min-h-screen bg-gray-50">
      {/* Componentes */}
    </div>
  );
}

// app/[slug]/page.tsx - Artículo individual
export const revalidate = 300; // 5 minutos

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const article = await getArticle(params.slug);
  
  return {
    title: `🚨 ${article.title}`,
    description: article.excerpt,
    openGraph: {
      title: `🚨 ${article.title}`,
      description: article.excerpt,
      images: [{
        url: article.image_url,
        width: 1200,
        height: 630,
        alt: article.title,
      }],
    },
    twitter: {
      card: 'summary_large_image',
      images: [article.image_url],
    },
  };
}
```

#### Performance Móvil (Lighthouse 100/100)
```typescript
// components/ArticleCard.tsx
export default function ArticleCard({ article }: ArticleCardProps) {
  return (
    <article className="bg-white rounded-lg shadow-md overflow-hidden">
      <Image
        src={article.image_url}
        alt={article.title}
        width={400}
        height={225}
        className="w-full h-48 object-cover"
        priority={true} // Crítico para LCP
        sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
      />
      {/* Resto del componente */}
    </article>
  );
}
```

#### Inyección Dinámica de Ads
```typescript
// lib/adInjector.ts
export function injectAds(content: string): string {
  const paragraphs = content.split('</p>');
  
  if (paragraphs.length > 2) {
    paragraphs.splice(2, 0, '</p><NativeAd />');
  }
  
  return paragraphs.join('</p>');
}

// app/[slug]/page.tsx
export default async function ArticlePage({ params }: Props) {
  const article = await getArticle(params.slug);
  const contentWithAds = injectAds(article.content || '');
  
  return (
    <article>
      <h1>{article.title}</h1>
      <div dangerouslySetInnerHTML={{ __html: contentWithAds }} />
    </article>
  );
}
```

---

## 4. Ingeniería de Viralidad (Open Graph)

### Configuración de Metadatos
```typescript
// app/[slug]/page.tsx
export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const article = await getArticle(params.slug);
  
  // Inyectar prefijo dinámico 🚨 para noticias urgentes
  const isUrgent = article.category === 'Urgente' || article.category === 'Policial';
  const prefix = isUrgent ? '🚨 ' : '';
  
  return {
    title: `${prefix}${article.title}`,
    description: article.excerpt,
    openGraph: {
      title: `${prefix}${article.title}`,
      description: article.excerpt,
      type: 'article',
      publishedTime: article.published_at,
      authors: ['Diario Virtual'],
      images: [{
        url: article.image_url,
        width: 1200,
        height: 630,
        alt: article.title,
      }],
    },
    twitter: {
      card: 'summary_large_image',
      images: [article.image_url],
    },
  };
}
```

### Web Share API
```typescript
// components/ShareWhatsApp.tsx
'use client';

export default function ShareWhatsApp({ title, url }: ShareWhatsAppProps) {
  const handleShare = async () => {
    const text = `🚨 ${title} - ${url}`;
    
    // Prioridad 1: Web Share API nativo para móviles
    if (typeof navigator !== 'undefined' && navigator.share) {
      try {
        await navigator.share({
          title: title,
          text: text,
          url: url,
        });
      } catch (error) {
        // User cancelled share
      }
    } else {
      // Fallback a wa.me codificado
      const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
      if (typeof window !== 'undefined') {
        window.open(whatsappUrl, '_blank');
      }
    }
  };
  
  return (
    <button
      onClick={handleShare}
      className="bg-green-600 text-white p-2 rounded-lg hover:bg-green-700 transition-colors"
      title="Compartir por WhatsApp"
    >
      <MessageCircle size={20} />
    </button>
  );
}
```

---

## 5. Protocolo de Seguridad y Estabilidad

### Rate Limiting
```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        \Illuminate\Routing\Middleware\ThrottleRequests::class.':60,1',
    ]);
})
```

### CORS
```php
// config/cors.php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST'],
    'allowed_origins' => [
        'https://diariomalleco.cl',
        'https://www.diariomalleco.cl',
    ],
    'allowed_headers' => ['Content-Type', 'Authorization'],
];
```

### Edge Middleware (Vercel)
```typescript
// middleware.ts
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function middleware(request: NextRequest) {
  // Optimización de headers de seguridad
  const response = NextResponse.next();
  
  response.headers.set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
  response.headers.set('X-Content-Type-Options', 'nosniff');
  response.headers.set('X-Frame-Options', 'DENY');
  response.headers.set('X-XSS-Protection', '1; mode=block');
  
  return response;
}

export const config = {
  matcher: '/:path*',
};
```

---

## 6. Definición de Hecho (Definition of Done)

- [ ] Latencia de API < 150ms
- [ ] Carga total de página en redes 4G < 1.2s
- [ ] Miniaturas de WhatsApp visualizándose en formato grande (1200x630)
- [ ] Scraper funcionando sin duplicados en un ciclo de 24h
- [ ] Health check de Redis y Postgres en verde

---

## 7. Instrucción de Arranque (Sprint 1)

**Equipo, el foco de la primera semana es el Pipeline de Datos. Necesito la API de Laravel exponiendo los feeds scrapeados y el boilerplate de Next.js conectado con ISR. No pierdan tiempo en CSS complejo; prioricen la velocidad de respuesta y la integridad de los metadatos OG.**

---

## 8. Métricas de Éxito

### Performance
- **First Contentful Paint**: < 400ms
- **Largest Contentful Paint**: < 1.2s
- **Time to Interactive**: < 800ms
- **Bundle Size**: < 50KB (gzipped)

### Negocio
- **CTR WhatsApp**: > 15%
- **Tasa de conversión**: 2-3%
- **Tiempo en página**: > 2:30 min
- **Tasa de rebote**: < 10%

---

## 9. Arquitectura de Deploy

### Backend (DigitalOcean)
```yaml
# docker-compose.yml
version: '3.8'
services:
  app:
    build: ./backend
    ports:
      - "8001:80"
    environment:
      - DB_HOST=postgres
      - CACHE_DRIVER=redis
      - QUEUE_CONNECTION=redis
    depends_on:
      - postgres
      - redis
  
  postgres:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: diariovirtual
      POSTGRES_USER: diariovirtual
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
  
  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data

volumes:
  postgres_data:
  redis_data:
```

### Frontend (Vercel)
```json
{
  "version": 2,
  "builds": [
    {
      "src": "package.json",
      "use": "@vercel/next"
    }
  ],
  "routes": [
    {
      "src": "/api/(.*)",
      "dest": "https://api.diariomalleco.cl/$1"
    }
  ],
  "env": {
    "NEXT_PUBLIC_API_URL": "https://api.diariomalleco.cl"
  }
}
```

---

## 10. Testing Strategy

### Backend
```php
// tests/Feature/ArticleApiTest.php
class ArticleApiTest extends TestCase
{
    public function test_can_get_articles()
    {
        Article::factory()->count(10)->create();
        
        $response = $this->get('/api/v1/articles');
        
        $response->assertStatus(200)
                ->assertJsonCount(10, 'data');
    }
    
    public function test_api_response_time_under_150ms()
    {
        Article::factory()->count(20)->create();
        
        $startTime = microtime(true);
        $response = $this->get('/api/v1/articles');
        $endTime = microtime(true);
        
        $responseTime = ($endTime - $startTime) * 1000;
        
        $this->assertLessThan(150, $responseTime);
    }
}
```

### Frontend
```typescript
// __tests__/components/ArticleCard.test.tsx
import { render, screen } from '@testing-library/react';
import ArticleCard from '@/components/ArticleCard';

describe('ArticleCard', () => {
  it('renders article with correct viral formatting', () => {
    const mockArticle = {
      id: '1',
      title: 'Título de prueba',
      slug: 'titulo-de-prueba',
      excerpt: 'Extracto de prueba',
      image_url: 'https://example.com/image.jpg',
      is_external: true,
      external_url: 'https://example.com',
      status: 'published',
      published_at: '2026-03-01T00:00:00Z'
    };
    
    render(<ArticleCard article={mockArticle} />);
    
    expect(screen.getByText('🚨 Título de prueba')).toBeInTheDocument();
    expect(screen.getByText('Extracto de prueba')).toBeInTheDocument();
  });
});
```

---

## 11. Monitoring y Observabilidad

### Health Check Endpoint
```php
// routes/api.php
Route::get('/api/v1/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'error',
        'redis' => Cache::driver('redis')->get('health_check') ? 'connected' : 'error',
        'version' => '1.0.0',
    ]);
});
```

### Performance Monitoring
```typescript
// lib/analytics.ts
export function trackPageView(page: string) {
  if (typeof window !== 'undefined' && 'gtag' in window) {
    (window as any).gtag('config', 'GA_MEASUREMENT_ID', {
      page_title: page,
      page_location: window.location.href,
    });
  }
}

export function trackShare(articleTitle: string, platform: string) {
  if (typeof window !== 'undefined' && 'gtag' in window) {
    (window as any).gtag('event', 'share', {
      article_title: articleTitle,
      platform: platform,
    });
  }
}
```

---

## 12. Roadmap de Implementación

### Sprint 1 (Semana 1)
- [ ] API Laravel con endpoints básicos
- [ ] Scraper RSS con source_hash
- [ ] Next.js boilerplate con ISR
- [ ] Conexión frontend-backend

### Sprint 2 (Semana 2)
- [ ] Optimización de imágenes (Image Proxy)
- [ ] Implementación de caché Redis con tags
- [ ] Componentes virales (ShareWhatsApp)
- [ ] Metadatos OpenGraph optimizados

### Sprint 3 (Semana 3)
- [ ] Inyección dinámica de anuncios
- [ ] Testing E2E con Playwright
- [ ] Optimización Lighthouse 100/100
- [ ] Deploy en staging

### Sprint 4 (Semana 4)
- [ ] Deploy en producción
- [ ] Monitoreo y analytics
- [ ] Optimización basada en métricas
- [ ] Documentación final

---

**Contacto Técnico**: Mauricio Morales  
**Email**: mauricio@diariomalleco.cl  
**Teléfono**: +56 9 3893 8614

---

*Esta especificación está actualizada a Marzo 2026 y debe ser seguida estrictamente para garantizar el éxito del MVP.*
