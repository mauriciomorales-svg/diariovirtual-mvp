'use client';

import { Article } from '@/types/article';
import ShareWhatsApp from './ShareWhatsApp';
import { getProxiedImageUrl } from '@/lib/image';

interface ArticleCardProps {
  article: Article;
}

export default function ArticleCard({ article }: ArticleCardProps) {
  const formattedDate = new Date(article.published_at).toLocaleDateString('es-CL', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });

  const imageSrc = getProxiedImageUrl(article.image_url, { title: article.title, slug: article.slug });

  if (article.is_external) {
    return (
      <article className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
        <img
          src={imageSrc}
          alt={article.title}
          className="w-full h-48 object-cover"
        />
        <div className="p-4">
          <div className="text-sm text-gray-500 mb-2">{formattedDate}</div>
          <h2 className="text-xl font-bold text-gray-900 mb-2 line-clamp-2">
            🚨 {article.title}
          </h2>
          <p className="text-gray-600 mb-4 line-clamp-3">{article.excerpt}</p>
          <div className="flex gap-2">
            <a
              href={article.external_url}
              target="_blank"
              rel="noopener noreferrer"
              className="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg text-center hover:bg-blue-700 transition-colors"
            >
              Leer Noticia
            </a>
            <ShareWhatsApp
              title={article.title}
              slug={article.slug}
            />
          </div>
        </div>
      </article>
    );
  }

  return (
    <article className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
      <img
        src={imageSrc}
        alt={article.title}
        className="w-full h-48 object-cover"
      />
      <div className="p-4">
        <div className="text-sm text-gray-500 mb-2">{formattedDate}</div>
        <h2 className="text-xl font-bold text-gray-900 mb-2 line-clamp-2">
          🚨 {article.title}
        </h2>
        <p className="text-gray-600 mb-4 line-clamp-3">{article.excerpt}</p>
          <div className="flex gap-2">
            <a
              href={`/${article.slug}`}
              className="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg text-center hover:bg-blue-700 transition-colors"
            >
              Leer Noticia
            </a>
            <ShareWhatsApp
              title={article.title}
              slug={article.slug}
            />
          </div>
      </div>
    </article>
  );
}
