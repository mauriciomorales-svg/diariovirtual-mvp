/**
 * URL base del backend API (Laravel)
 */
const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000';

const NO_PROXY_DOMAINS = ['localhost', '127.0.0.1'];

/** Si la API devuelve placeholder genérico, generamos uno único por artículo */
const EXTERNAL_PLACEHOLDER = 'via.placeholder.com';

/**
 * Comprueba si una URL es externa y debe pasar por el proxy
 */
function needsProxy(url: string): boolean {
  if (!url || typeof url !== 'string') return false;
  const trimmed = url.trim();
  if (!trimmed.startsWith('http://') && !trimmed.startsWith('https://')) return false;
  try {
    const parsed = new URL(trimmed);
    return !NO_PROXY_DOMAINS.some(d => parsed.hostname === d || parsed.hostname.endsWith('.' + d));
  } catch {
    return false;
  }
}

/**
 * Codifica una URL en base64url (seguro para rutas HTTP: sin / ni +)
 */
function safeBase64Encode(url: string): string {
  try {
    const base64 = btoa(encodeURIComponent(url));
    return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
  } catch {
    const base64 = btoa(url);
    return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
  }
}

export interface ImageOptions {
  title?: string;
  slug?: string;
}

/** Rutas relativas - Next.js las reescribe al backend (mismo origen = carga fiable) */
const STATIC_PLACEHOLDER = '/img/placeholder.svg';
const DYNAMIC_PLACEHOLDER = (t: string, s: string) => `/img/placeholder-img?s=${encodeURIComponent(s)}&t=${encodeURIComponent(t)}`;

/**
 * Devuelve la URL de la imagen. Si es placeholder genérico, genera uno único por artículo.
 */
export function getProxiedImageUrl(imageUrl: string, options?: ImageOptions): string {
  const url = (imageUrl && typeof imageUrl === 'string' && imageUrl.trim()) ? imageUrl.trim() : '';
  const isPlaceholder = !url || url.includes(EXTERNAL_PLACEHOLDER);

  if (isPlaceholder && (options?.slug || options?.title)) {
    return DYNAMIC_PLACEHOLDER(options.title || '', options.slug || options.title || '');
  }
  if (isPlaceholder) {
    return STATIC_PLACEHOLDER;
  }
  if (!needsProxy(url)) {
    if (url.startsWith('http') && (url.includes('127.0.0.1') || url.includes('localhost'))) {
      const path = new URL(url).pathname;
      return path.startsWith('/images/') ? `/img${path}` : url;
    }
    return url;
  }
  const encoded = safeBase64Encode(url);
  return `/img/proxy/${encoded}`;
}
