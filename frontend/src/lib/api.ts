import { Article } from '@/types/article';

// API URL - servidor principal PHP en puerto 8091
const API_URL = 'http://127.0.0.1:8091';

export async function getArticles(): Promise<{ articles: Article[]; total: number; showing: string }> {
  try {
    // Try batch API first (most reliable)
    const batchRes = await fetch('http://127.0.0.1:8000/batch-articles-api.php', {
      cache: 'no-store'
    });
    if (batchRes.ok) {
      const batchData = await batchRes.json();
      return {
        articles: batchData.data || [],
        total: batchData.total || 0,
        showing: batchData.showing || ''
      };
    }
  } catch (batchError) {
    console.error('Batch API Error:', batchError);
  }

  try {
    // Fallback to main API
    const res = await fetch(`${API_URL}/articles-api.php`, {
      next: { revalidate: 60 },
    });

    if (!res.ok) {
      throw new Error(`Failed to fetch articles: ${res.status} ${res.statusText}`);
    }

    const data = await res.json();
    return {
      articles: data.data || [],
      total: data.total || 0,
      showing: data.showing || ''
    };
  } catch (error) {
    console.error('Main API Error:', error);
    // Return empty data instead of throwing
    return {
      articles: [],
      total: 0,
      showing: 'No articles available'
    };
  }
}

export async function getArticle(slug: string): Promise<Article> {
  const res = await fetch(`${API_URL}/article-api.php?slug=${slug}`, {
    next: { revalidate: 3600 },
  });

  if (!res.ok) {
    throw new Error(`Failed to fetch article: ${res.status} ${res.statusText}`);
  }

  return res.json();
}
