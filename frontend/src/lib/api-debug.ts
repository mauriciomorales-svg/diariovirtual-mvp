export interface Article {
  id: string;
  title: string;
  slug: string;
  excerpt: string;
  content?: string;
  image_url: string;
  is_external: boolean;
  external_url?: string;
  status: string;
  published_at: string;
}

// Mock data para demostración
const mockArticles: Article[] = [
  {
    id: '1',
    title: '🚨 Llaman a vacunarse oportunamente contra la Influenza y el Covid-19',
    slug: 'llaman-a-vacunarse-oportunamente-contra-la-influenza-y-el-covid-19',
    excerpt: 'Mañana domingo 1 de marzo parte oficialmente la campaña de vacunación. Seremi de Salud de La Araucanía llama a la comunidad a inmunizarse.',
    image_url: 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Virtual',
    is_external: true,
    external_url: 'https://www.malleco7.cl/llaman-a-vacunarse-oportunamente-contra-la-influenza-y-el-covid-19/',
    status: 'published',
    published_at: '2026-03-01T04:06:28.000000Z'
  },
  {
    id: '2',
    title: '🚨 Hospital de Curacautín inaugura primera etapa de su nueva y moderna infraestructura',
    slug: 'hospital-de-curacautin-inaugura-primera-etapa-de-su-nueva-y-moderna-infraestructura',
    excerpt: 'La obra contempla 8.870 metros cuadrados construidos y forma parte del Plan Nacional de Inversiones en Salud.',
    image_url: 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Virtual',
    is_external: true,
    external_url: 'https://www.malleco7.cl/hospital-de-curacautin-inaugura-primera-etapa-de-su-nueva-y-moderna-infraestructura/',
    status: 'published',
    published_at: '2026-03-01T04:06:28.000000Z'
  },
  {
    id: '3',
    title: '🚨 Vuelve el Festival de la Pizza y de las Artes en Angol',
    slug: 'vuelve-el-festival-de-la-pizza-y-de-las-artes-en-angol',
    excerpt: 'La segunda versión del evento se realizará desde este viernes 27 de febrero al domingo 1 de marzo en el Parque Escuela Normal.',
    image_url: 'https://via.placeholder.com/1200x630/333333/ffffff?text=Diario+Virtual',
    is_external: true,
    external_url: 'https://www.malleco7.cl/vuelve-el-festival-de-la-pizza-y-de-las-artes-en-angol/',
    status: 'published',
    published_at: '2026-03-01T04:06:28.000000Z'
  }
];

export async function getArticles(): Promise<Article[]> {
  // Simular delay de red
  await new Promise(resolve => setTimeout(resolve, 500));
  return mockArticles;
}

export async function getArticle(slug: string): Promise<Article> {
  await new Promise(resolve => setTimeout(resolve, 300));
  const article = mockArticles.find(a => a.slug === slug);
  if (!article) {
    throw new Error('Article not found');
  }
  return article;
}
