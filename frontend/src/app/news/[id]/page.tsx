import { getArticleById } from '@/lib/api';
import ArticleDetailView from '@/components/ArticleDetailView';
import { getProxiedImageUrl } from '@/lib/image';
import { notFound } from 'next/navigation';
import type { Article } from '@/types/article';

const SITE_ORIGIN = (process.env.NEXT_PUBLIC_SITE_URL || 'https://diariozonasur.cl').replace(
  /\/$/,
  ''
);

function normalizeMetadata(raw: unknown): Record<string, unknown> {
  if (!raw) return {};
  if (typeof raw === 'string') {
    try {
      return JSON.parse(raw) as Record<string, unknown>;
    } catch {
      return {};
    }
  }
  if (typeof raw === 'object') {
    return raw as Record<string, unknown>;
  }
  return {};
}

function absolutePublicUrl(pathOrUrl: string): string {
  const s = (pathOrUrl || '').trim();
  if (!s) return `${SITE_ORIGIN}/img/placeholder.svg`;
  if (s.startsWith('http://') || s.startsWith('https://')) return s;
  return `${SITE_ORIGIN}${s.startsWith('/') ? '' : '/'}${s}`;
}

function buildMetadata(article: Article, pathForCanonical: string) {
  const imagePath = getProxiedImageUrl(article.image_url, {
    title: article.title,
    slug: article.slug,
  });
  const imageUrl = absolutePublicUrl(imagePath);
  const metadata = normalizeMetadata(article.metadata);
  const originalUrl = article.external_url || (metadata.original_url as string | undefined);

  const metadataConfig: Record<string, unknown> = {
    title: `${article.title} | Diario Zona Sur`,
    description: article.excerpt,
    keywords: ['Zona Sur', 'Araucanía', 'Chile', 'noticias'],
    authors: [{ name: 'Diario Zona Sur' }],
    openGraph: {
      title: article.title,
      description: article.excerpt,
      url: `${SITE_ORIGIN}${pathForCanonical}`,
      siteName: 'Diario Zona Sur',
      locale: 'es_CL',
      type: 'article',
      publishedTime: article.published_at,
      images: [{ url: imageUrl, width: 1200, height: 630, alt: article.title }],
    },
    twitter: {
      card: 'summary_large_image',
      title: article.title,
      description: article.excerpt,
      images: [imageUrl],
    },
    alternates: {} as { canonical?: string },
    robots: {
      index: true,
      follow: true,
      'max-image-preview': 'large',
      'max-snippet': -1,
    },
  };

  if (article.is_external && originalUrl) {
    (metadataConfig.alternates as { canonical: string }).canonical = originalUrl;
  } else {
    (metadataConfig.alternates as { canonical: string }).canonical =
      `${SITE_ORIGIN}${pathForCanonical}`;
  }

  return metadataConfig;
}

export async function generateMetadata({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const article = await getArticleById(id);
  if (!article) {
    notFound();
  }
  return buildMetadata(article, `/news/${encodeURIComponent(id)}`);
}

export default async function NewsByIdPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const article = await getArticleById(id);
  if (!article) {
    notFound();
  }
  return <ArticleDetailView article={article} />;
}
