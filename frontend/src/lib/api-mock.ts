// Mock data for testing
const mockArticles = [
  {
    id: '1',
    title: '🚨 Noticia de prueba 1',
    slug: 'noticia-prueba-1',
    excerpt: 'Este es un extracto de prueba para la noticia 1',
    content: 'Contenido completo de la noticia de prueba 1',
    image_url: 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco',
    published_at: '2026-03-01T20:00:00Z',
    is_external: false,
    external_url: null
  },
  {
    id: '2',
    title: '🚨 Noticia de prueba 2',
    slug: 'noticia-prueba-2',
    excerpt: 'Este es un extracto de prueba para la noticia 2',
    content: 'Contenido completo de la noticia de prueba 2',
    image_url: 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Malleco',
    published_at: '2026-03-01T19:00:00Z',
    is_external: false,
    external_url: null
  }
];

export async function getArticles(): Promise<{ articles: any[]; total: number; showing: string }> {
  console.log('Using mock data');
  return {
    articles: mockArticles,
    total: mockArticles.length,
    showing: `Showing ${mockArticles.length} mock articles`
  };
}

export async function getArticle(slug: string): Promise<any> {
  return mockArticles.find(a => a.slug === slug) || mockArticles[0];
}
