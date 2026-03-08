import { getArticle } from '@/lib/api';
import { injectAds } from '@/lib/adInjector';
import NativeAd from '@/components/NativeAd';
import AdInjector from '@/components/AdInjector';
import ShareWhatsApp from '@/components/ShareWhatsApp';
import Image from 'next/image';

export async function generateMetadata({ params }: { params: { slug: string } }) {
  const article = await getArticle(params.slug);
  
  return {
    title: `🚨 ${article.title}`,
    description: article.excerpt,
    openGraph: {
      title: `🚨 ${article.title}`,
      description: article.excerpt,
      images: [{
        url: article.image_url,
        width: 1200,
        height: 630,
        alt: article.title,
      }],
    },
    twitter: {
      card: 'summary_large_image',
      images: [article.image_url],
    },
  };
}

export default async function ArticlePage({ params }: { params: { slug: string } }) {
  const article = await getArticle(params.slug);
  
  // Inject native ads after second paragraph
  const contentWithAds = injectAds(article.content || '');
  
  // Replace placeholder with actual component marker
  const processedContent = contentWithAds.replace('<div data-native-ad="true"></div>', '<div id="native-ad-placeholder"></div>');
  
  return (
    <div className="min-h-screen bg-gray-50">
      <article className="max-w-4xl mx-auto px-4 py-8">
        <header className="mb-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            🚨 {article.title}
          </h1>
          <div className="flex items-center gap-4 text-sm text-gray-600">
            <time dateTime={article.published_at}>
              {new Date(article.published_at).toLocaleDateString('es-CL', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
              })}
            </time>
            <span>•</span>
            <span>Diario Virtual</span>
          </div>
        </header>
        
        <div className="mb-8">
          <Image
            src={article.image_url}
            alt={article.title}
            width={1200}
            height={630}
            className="w-full h-auto rounded-lg shadow-lg"
            priority={true}
            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 100vw, 800px"
          />
        </div>
        
        <div className="prose prose-lg max-w-none">
          <div 
            dangerouslySetInnerHTML={{ __html: processedContent }}
            className="text-gray-700 leading-relaxed"
          />
        </div>
        
        {/* Inject NativeAd component dynamically */}
        <AdInjector content={processedContent} />
        
        <div className="mt-12 flex items-center justify-between border-t pt-8">
          <div className="flex gap-4">
            <ShareWhatsApp
              title={article.title}
              slug={article.slug}
            />
          </div>
          
          {article.is_external && article.external_url && (
            <a
              href={article.external_url}
              target="_blank"
              rel="noopener noreferrer"
              className="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
            >
              Leer Noticia Original
            </a>
          )}
        </div>
      </article>
    </div>
  );
}
