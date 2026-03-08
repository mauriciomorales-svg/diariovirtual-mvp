import { Article } from '@/types/article';

export async function getArticles(): Promise<{ articles: Article[]; total: number; showing: string }> {
  try {
    // Direct batch API call with proper headers
    const batchRes = await fetch('http://127.0.0.1:8000/batch-articles-api.php', {
      cache: 'no-store',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });
    
    if (batchRes.ok) {
      const batchData = await batchRes.json();
      console.log('Batch API response:', batchData);
      return {
        articles: batchData.data || [],
        total: batchData.total || 0,
        showing: batchData.showing || ''
      };
    } else {
      console.error('Batch API failed:', batchRes.status, batchRes.statusText);
      throw new Error(`Batch API failed: ${batchRes.status}`);
    }
  } catch (error) {
    console.error('API Error:', error);
    // Return empty data instead of throwing
    return {
      articles: [],
      total: 0,
      showing: 'No articles available'
    };
  }
}

export async function getArticle(slug: string): Promise<Article> {
  throw new Error('Article detail not implemented');
}
