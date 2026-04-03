import { Article } from '@/types/article';

/**
 * Slug guardado como dominio por error (ej. diariozonasur.cl) — no debe usarse como path /slug.
 */
export function slugLooksLikeHostname(slug: string): boolean {
  const s = slug.trim().toLowerCase();
  if (!s.includes('.') || s.includes('/') || s.includes(' ')) {
    return false;
  }
  if (/\.(jpg|jpeg|png|gif|webp|svg)$/i.test(s)) {
    return false;
  }
  const parts = s.split('.');
  if (parts.length < 2) {
    return false;
  }
  const tld = parts[parts.length - 1];
  return tld.length >= 2 && tld.length <= 24 && /^[a-z]+$/i.test(tld);
}

/**
 * Ruta interna o URL externa para "Leer más" / compartir.
 */
export function publicArticlePath(article: Article): string {
  if (article.is_external && article.external_url) {
    return article.external_url;
  }
  const slug = (article.slug || '').trim();
  if (!slug) {
    return `/news/${encodeURIComponent(article.id)}`;
  }
  if (slugLooksLikeHostname(slug)) {
    return `/news/${encodeURIComponent(article.id)}`;
  }
  return `/${encodeURIComponent(slug)}`;
}
