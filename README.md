# DiarioVirtual - "El Diario de Malleco"

## Motor de Tráfico Web Ultrarrápido

Portal de noticias locales (Provincia de Malleco) diseñado para inyectar tráfico masivo y viral hacia landing pages de negocios B2C/B2B.

## Stack Tecnológico

- **Backend**: Laravel 12 + PostgreSQL + Redis
- **Frontend**: Next.js 14 + TypeScript + Tailwind CSS
- **Admin**: FilamentPHP
- **Deploy**: DigitalOcean (Laravel) + Vercel (Next.js)

## Arquitectura

### Backend (Laravel)
- API Headless RESTful con caching Redis
- Scraper RSS asíncrono (cada 30 min)
- Panel admin Filament para gestión de artículos

### Frontend (Next.js)
- ISR/SSR para velocidad extrema (<1s en 3G/4G)
- Componentes de viralidad WhatsApp
- Inyección dinámica de anuncios nativos

### Base de Datos
- Modelo `Article` minimalista y escalable
- Índices optimizados para lecturas masivas
- Soporte para contenido propio y scrapeado

## Características Clave

- 🚀 Velocidad extrema con ISR/Redis caching
- 📱 Mobile-first design
- 🔄 Viralidad WhatsApp con OpenGraph optimizado
- 📈 Anuncios nativos hardcodeados para negocios
- 🤖 Automatización RSS scraper

## Estructura del Proyecto

```
diariovirtual/
├── backend/          # Laravel API
├── frontend/         # Next.js App
├── docs/            # Documentación técnica
└── deploy/          # Configuraciones deploy
```

## Objetivo Principal

Generar tráfico viral hacia:
- Minimarket Donde Morales (B2C)
- JobsHours/ObraControl (B2B)

Mediante anuncios nativos integrados en el flujo de lectura de noticias.
