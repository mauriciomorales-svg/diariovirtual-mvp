import { Article } from '@/types/article';

const BACKEND = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000';

function normalizeArticle(a: Record<string, unknown>): Article {
  return {
    id: String(a.id ?? ''),
    title: String(a.title ?? ''),
    slug: String(a.slug ?? ''),
    source_hash: String(a.source_hash ?? ''),
    excerpt: String(a.excerpt ?? ''),
    image_url: String(a.image_url || 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco'),
    is_external: Boolean(a.is_external),
    external_url: a.external_url ? String(a.external_url) : undefined,
    status: String(a.status ?? 'published'),
    published_at: String(a.published_at ?? a.created_at ?? new Date().toISOString()),
    content: a.content ? String(a.content) : undefined,
  };
}

export async function getArticles(): Promise<{ articles: Article[]; total: number; showing: string }> {
  const urls = [
    `${BACKEND}/api/batch-articles`,
    `${BACKEND}/batch-articles-api.php`,
    `${BACKEND}/api/v1/articles.php`,
  ];

  for (const url of urls) {
    try {
      const batchRes = await fetch(url, {
        cache: 'no-store',
        headers: { Accept: 'application/json' },
      });
      if (batchRes.ok) {
        const batchData = await batchRes.json();
        const data = batchData.data ?? [];
        const articles = Array.isArray(data) ? data.map(normalizeArticle) : [];
        return {
          articles,
          total: batchData.total ?? articles.length,
          showing: batchData.showing ?? `Mostrando ${articles.length} noticias`,
        };
      }
    } catch (e) {
      console.warn(`API ${url} falló:`, e);
    }
  }

  return { articles: [], total: 0, showing: 'No hay noticias disponibles' };
}

export async function getArticle(slug: string): Promise<Article> {
  throw new Error('Article detail not implemented');
}
