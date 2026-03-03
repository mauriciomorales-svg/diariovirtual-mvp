# DiarioVirtual - Guía de Ejecución Local

Guía completa para ejecutar el sistema DiarioVirtual con integración Gemini AI en tu entorno local.

## 📋 Requisitos Previos

- **PHP 8.2+** con extensiones: sqlite, pdo_sqlite, mbstring, openssl
- **Composer** (gestor de dependencias PHP)
- **Node.js 18+** y **npm**
- **WAMP/XAMPP** o servidor web similar (opcional)

---

## 🚀 Iniciar el Backend (Laravel)

### 1. Configuración Inicial (solo una vez)

```bash
# Ir al directorio del backend
cd c:\wamp64\www\diariovirtual\backend

# Instalar dependencias (si no están instaladas)
composer install

# Configurar variables de entorno
cp .env.example .env
php artisan key:generate
```

### 2. Configurar API Key de Gemini

Editar el archivo `backend/.env` y agregar tu API key:

```env
# Google Gemini AI Configuration
GEMINI_API_KEY=tu_api_key_aqui
GEMINI_PROJECT_ID=diariovirtual-prod
GEMINI_MODEL=gemini-1.5-flash
```

> **Nota**: Obtén tu API key gratuita en [Google AI Studio](https://makersuite.google.com/app/apikey)

### 3. Base de Datos

La base de datos SQLite ya está configurada y migrada:

```bash
# Verificar estado de migraciones
php artisan migrate:status

# Si necesitas recrear la base de datos:
php artisan migrate:fresh --seed
```

### 4. Iniciar Servidor de Desarrollo

```bash
# Opción A: Usando PHP built-in server (puerto 8000)
php artisan serve

# Opción B: Usando WAMP (acceso via http://localhost/diariovirtual/backend/public)
```

El backend estará disponible en: `http://localhost:8000`

---

## 🎨 Iniciar el Frontend (Next.js)

### 1. Instalar Dependencias (solo una vez)

```bash
# Ir al directorio del frontend
cd c:\wamp64\www\diariovirtual\frontend

# Instalar dependencias (ya están instaladas)
npm install
```

### 2. Iniciar Servidor de Desarrollo

```bash
# Iniciar Next.js (puerto 3000 por defecto)
npm run dev
```

El frontend estará disponible en: `http://localhost:3000`

---

## 🔗 URLs Importantes

### Frontend
- **Home**: http://localhost:3000
- **Artículo**: http://localhost:3000/[slug]

### Backend API
- **API Artículos**: http://localhost:8000/api/articles
- **API Artículo**: http://localhost:8000/api/articles/{slug}

### Panel de Administración Gemini (Sin autenticación - Desarrollo)
- **Importación Simple**: http://localhost:8000/dev/gemini/import
- **Procesamiento Batch**: http://localhost:8000/dev/gemini/batch
- **Procesamiento Enhanced**: http://localhost:8000/dev/gemini/enhanced
- **Health Check**: http://localhost:8000/dev/gemini/health
- **Estadísticas**: http://localhost:8000/dev/gemini/stats

### Panel de Administración Gemini (Con autenticación - Producción)
- **Importación Simple**: http://localhost:8000/admin/gemini/import
- **Procesamiento Batch**: http://localhost:8000/admin/gemini/batch
- **Procesamiento Enhanced**: http://localhost:8000/admin/gemini/enhanced

**Credenciales de prueba:**
- Email: `test@example.com`
- Password: `password`

---

## 🧪 Probar el Sistema

### 1. Verificar Health Check de Gemini

Abrir en navegador:
```
http://localhost:8000/dev/gemini/health
```

Debería mostrar el estado del servicio Gemini.

### 2. Procesar un Artículo

1. Ir a: http://localhost:8000/dev/gemini/enhanced
2. Pegar el contenido de una noticia en el formulario
3. Configurar opciones (temperatura, longitud, estilo local)
4. Click en "Procesar con Gemini"
5. Revisar el resultado transformado

### 3. Ver Artículos en el Frontend

1. Abrir: http://localhost:3000
2. Debería mostrar la lista de artículos (o datos de ejemplo si el backend no está corriendo)

---

## 🛠️ Comandos Útiles

### Backend (Laravel)

```bash
# Limpiar cachés
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver rutas disponibles
php artisan route:list

# Ver rutas de desarrollo Gemini
php artisan route:list --path=dev

# Ejecutar trabajos de cola (para procesamiento asíncrono)
php artisan queue:work

# Ver estado de las migraciones
php artisan migrate:status
```

### Frontend (Next.js)

```bash
# Desarrollo
npm run dev

# Build de producción
npm run build

# Iniciar en modo producción
npm start

# Linting
npm run lint
```

---

## 🔧 Solución de Problemas

### Error: "Failed to fetch articles"
- Verificar que el backend esté corriendo en el puerto 8000
- Verificar la configuración CORS en `backend/config/cors.php`
- Revisar logs en `backend/storage/logs/laravel.log`

### Error: "API Key not configured"
- Verificar que `GEMINI_API_KEY` esté configurada en `backend/.env`
- Ejecutar `php artisan config:clear` para recargar configuración

### Error de CORS
- La configuración CORS ya permite todas las solicitudes en desarrollo
- Verificar que `APP_ENV=local` en `backend/.env`

### Base de datos bloqueada (SQLite)
- Cerrar cualquier programa que esté accediendo al archivo `.sqlite`
- Verificar permisos del archivo `database/database.sqlite`

---

## 📁 Estructura de Archivos Importantes

```
diariovirtual/
├── backend/
│   ├── .env                          # Variables de entorno
│   ├── app/
│   │   ├── Services/
│   │   │   └── GeminiService.php     # Servicio de integración Gemini
│   │   ├── Http/Controllers/Admin/
│   │   │   ├── GeminiController.php
│   │   │   ├── GeminiBatchController.php
│   │   │   └── GeminiEnhancedController.php
│   │   └── Jobs/
│   │       └── TransformNewsJob.php  # Job de procesamiento
│   ├── config/
│   │   ├── cors.php                  # Configuración CORS
│   │   └── services.php              # Configuración de servicios
│   ├── database/
│   │   └── database.sqlite           # Base de datos SQLite
│   ├── routes/
│   │   ├── web.php                   # Rutas web (incluye /dev/gemini/*)
│   │   └── api.php                   # Rutas API
│   └── resources/views/admin/gemini/
│       ├── import.blade.php
│       ├── batch.blade.php
│       └── import-enhanced.blade.php
│
└── frontend/
    ├── next.config.ts                # Configuración Next.js + proxy API
    ├── src/
    │   ├── lib/
    │   │   └── api.ts                # Cliente API
    │   └── components/
    │       ├── GeminiContentProcessor.tsx
    │       └── GeminiEnhancedProcessor.tsx
    └── package.json
```

---

## 🎯 Flujo de Trabajo Recomendado

1. **Iniciar Backend**: `php artisan serve` (en directorio backend)
2. **Iniciar Frontend**: `npm run dev` (en directorio frontend)
3. **Abrir Panel Gemini**: http://localhost:8000/dev/gemini/enhanced
4. **Procesar Noticias**: Pegar contenido y transformar con Gemini
5. **Ver Resultados**: http://localhost:3000

---

## 📚 Documentación Adicional

- Documentación de implementación en `/docs/`
- Tests disponibles en `backend/tests/Feature/`

---

**Sistema listo para usar en local! 🚀**
