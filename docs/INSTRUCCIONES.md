# DiarioVirtual - Instrucciones de Instalación y Deploy

## Backend (Laravel)

### 1. Instalación Local
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan news:scrape  # Test scraper
```

### 2. Configuración .env
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5435
DB_DATABASE=diariovirtual
DB_USERNAME=diariovirtual
DB_PASSWORD=diariovirtual2026

CACHE_DRIVER=redis
REDIS_HOST=localhost
REDIS_PORT=6382

QUEUE_CONNECTION=redis
```

### 3. Deploy DigitalOcean
```bash
# En servidor
docker-compose up -d
docker exec diariovirtual-app php artisan migrate
docker exec diariovirtual-app php artisan news:scrape
```

## Frontend (Next.js)

### 1. Instalación Local
```bash
cd frontend
npm install
npm run dev  # Puerto 3003
```

### 2. Configuración .env.local
```env
NEXT_PUBLIC_API_URL=http://localhost:8001
```

### 3. Deploy Vercel
```bash
vercel --prod
```

## Flujo de Trabajo

### 1. Scraper Automático
- Cron job cada 30 minutos
- Extrae noticias de RSS feeds
- Guarda como artículos externos

### 2. Panel Admin (Filament)
- Acceso: `/admin`
- CRUD de artículos
- Programar publicaciones

### 3. Viralidad WhatsApp
- Emoji 🚨 en títulos
- Botón compartir nativo
- OpenGraph optimizado

## Anuncios Nativos

### Configuración
- Archivo: `frontend/src/data/ads.ts`
- 3 anuncios hardcodeados
- Minimarket, JobsHours, ObraControl

### Inyección en Artículos
- Cada 3 párrafos
- Diseño tipo "recomendado"
- CTA directo a negocio

## Monitoreo

### Logs
- Backend: `storage/logs/laravel.log`
- Scraper: `php artisan news:scrape --verbose`

### Caché
- Redis CLI: `redis-cli -p 6382`
- Limpiar: `FLUSHALL`

## Dominios

### Producción
- Frontend: `diariomalleco.cl` (Vercel)
- Backend: `api.diariomalleco.cl` (DigitalOcean)

### DNS
- A record: Vercel IP
- CNAME api: DigitalOcean IP

## Costos Mensuales

- DigitalOcean: $6-12 USD
- Vercel Pro: $20 USD
- Dominio .cl: ~$15 USD/año
- **Total**: ~$40-50 USD/mes
