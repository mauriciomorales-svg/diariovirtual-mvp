import { Article } from '@/types/article';

const LARAVEL_API = (process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');

function normalizeArticleFromApi(raw: Record<string, unknown>): Article {
  const published =
    raw.published_at != null && String(raw.published_at).trim() !== ''
      ? String(raw.published_at)
      : raw.created_at != null && String(raw.created_at).trim() !== ''
        ? String(raw.created_at)
        : new Date().toISOString();

  return {
    id: String(raw.id ?? ''),
    title: String(raw.title ?? ''),
    slug: String(raw.slug ?? ''),
    source_hash: String(raw.source_hash ?? ''),
    excerpt: String(raw.excerpt ?? ''),
    image_url: String(
      raw.image_url ||
        'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Zona+Sur'
    ),
    is_external: Boolean(raw.is_external),
    external_url: raw.external_url ? String(raw.external_url) : undefined,
    status: String(raw.status ?? 'published'),
    published_at: published,
    content: raw.content != null ? String(raw.content) : undefined,
    metadata: raw.metadata as Article['metadata'],
  };
}

export async function getArticles(): Promise<{ articles: Article[]; total: number; showing: string }> {
  try {
    // Try batch API first (most reliable)
    const batchRes = await fetch(`${LARAVEL_API}/batch-articles-api.php`, {
      cache: 'no-store',
    });
    if (batchRes.ok) {
      const batchData = await batchRes.json();
      const data = batchData.data || [];
      const articles = Array.isArray(data)
        ? data.map((row: Record<string, unknown>) => normalizeArticleFromApi(row))
        : [];
      return {
        articles,
        total: batchData.total || 0,
        showing: batchData.showing || '',
      };
    }
  } catch (batchError) {
    console.error('Batch API Error:', batchError);
  }

  try {
    // Fallback to Laravel API
    const res = await fetch(`${LARAVEL_API}/api/v1/articles`, {
      cache: 'no-store',
    });

    if (!res.ok) {
      throw new Error(`Failed to fetch articles: ${res.status} ${res.statusText}`);
    }

    const data = await res.json();
    const items = data.data || data;
    const list = Array.isArray(items) ? items : [];
    return {
      articles: list.map((row: Record<string, unknown>) => normalizeArticleFromApi(row)),
      total: data.total ?? list.length,
      showing: data.showing || 'Artículos recientes',
    };
  } catch (error) {
    console.error('Main API Error:', error);
    return {
      articles: [],
      total: 0,
      showing: 'No articles available',
    };
  }
}

/**
 * Detalle por slug. No lanza: devuelve null si no existe o falla la red (evita error digest en Next).
 */
export async function getArticle(slug: string): Promise<Article | null> {
  if (!slug || typeof slug !== 'string') {
    return null;
  }

  try {
    const res = await fetch(
      `${LARAVEL_API}/api/v1/articles/${encodeURIComponent(slug)}`,
      {
        cache: 'no-store',
        headers: { Accept: 'application/json' },
      }
    );

    if (res.status === 404) {
      return null;
    }

    if (!res.ok) {
      console.warn(`getArticle: HTTP ${res.status} for slug=${slug}`);
      return null;
    }

    const raw = (await res.json()) as Record<string, unknown>;
    if (!raw || typeof raw !== 'object' || !raw.slug) {
      return null;
    }

    return normalizeArticleFromApi(raw);
  } catch (e) {
    console.error('getArticle fetch error:', e);
    return null;
  }
}

/** Detalle por id UUID (ruta /news/[id] cuando el slug es inválido). */
export async function getArticleById(id: string): Promise<Article | null> {
  if (!id || typeof id !== 'string') {
    return null;
  }

  try {
    const res = await fetch(
      `${LARAVEL_API}/api/v1/article/by-id/${encodeURIComponent(id)}`,
      {
        cache: 'no-store',
        headers: { Accept: 'application/json' },
      }
    );

    if (res.status === 404) {
      return null;
    }

    if (!res.ok) {
      console.warn(`getArticleById: HTTP ${res.status} id=${id}`);
      return null;
    }

    const raw = (await res.json()) as Record<string, unknown>;
    if (!raw || typeof raw !== 'object' || !raw.id) {
      return null;
    }

    return normalizeArticleFromApi(raw);
  } catch (e) {
    console.error('getArticleById fetch error:', e);
    return null;
  }
}
