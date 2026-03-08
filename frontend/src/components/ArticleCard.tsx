'use client';

import { Article } from '@/types/article';
import ShareWhatsApp from './ShareWhatsApp';
import Image from 'next/image';

interface ArticleCardProps {
  article: Article;
}

export default function ArticleCard({ article }: ArticleCardProps) {
  const formattedDate = new Date(article.published_at).toLocaleDateString('es-CL', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });

  if (article.is_external) {
    return (
      <article className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
        <Image
          src={article.image_url}
          alt={article.title}
          width={400}
          height={225}
          className="w-full h-48 object-cover"
          priority={true} // Lighthouse 100/100 optimization
          sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
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
      <Image
        src={article.image_url}
        alt={article.title}
        width={400}
        height={225}
        className="w-full h-48 object-cover"
        priority={true}
        sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
      />
      <div className="p-4">
        <div className="text-sm text-gray-500 mb-2">{formattedDate}</div>
        <h2 className="text-xl font-bold text-gray-900 mb-2 line-clamp-2">
          🚨 {article.title}
        </h2>
        <p className="text-gray-600 mb-4 line-clamp-3">{article.excerpt}</p>
          <div className="flex gap-2">
            <a
              href={`/noticias/${article.slug}`}
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
