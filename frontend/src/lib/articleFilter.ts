import { Article } from '@/types/article';

function parseMetadata(article: Article): Record<string, unknown> {
  const raw = article.metadata;
  if (!raw) return {};
  if (typeof raw === 'string') {
    try {
      return JSON.parse(raw) as Record<string, unknown>;
    } catch {
      return {};
    }
  }
  return raw as Record<string, unknown>;
}

/** Filtra noticias por comuna/categoría (angol, renaico, victoria, etc.). */
export function filterArticlesByCategoria(
  articles: Article[],
  categoria: string
): Article[] {
  const key = categoria.trim().toLowerCase();
  if (!key) return articles;

  return articles.filter((article) => {
    const meta = parseMetadata(article);
    const metaComuna = String(meta.comuna ?? '')
      .trim()
      .toLowerCase();
    const metaCategoria = String(meta.categoria ?? '')
      .trim()
      .toLowerCase();
    const metaCat = metaCategoria || metaComuna;
    const metaRegion = String(meta.region ?? '')
      .trim()
      .toLowerCase();

    if (metaCategoria === key || metaComuna === key) return true;

    if (key === 'araucania' && metaRegion.includes('araucan')) return true;

    // Si metadata indica otra comuna, no incluir por slug/título ambiguo
    // (ej. "calle Angol de Renaico" con comuna Renaico).
    if (metaComuna && metaComuna !== key && key !== 'araucania' && key !== 'nacional') {
      return false;
    }

    const slug = (article.slug || '').toLowerCase();
    const title = (article.title || '').toLowerCase();

    return slug.includes(key) || title.includes(key) || metaCat === key;
  });
}

export function categoriaLabel(categoria: string): string {
  const labels: Record<string, string> = {
    angol: 'Angol',
    renaico: 'Renaico',
    victoria: 'Victoria',
    collipulli: 'Collipulli',
    araucania: 'La Araucanía',
    nacional: 'Nacional',
    malleco: 'Malleco',
  };
  return labels[categoria.toLowerCase()] ?? categoria;
}
