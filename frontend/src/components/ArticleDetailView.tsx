import AdminImageEditLink from '@/components/AdminImageEditLink';
import { injectAds } from '@/lib/adInjector';
import AdInjector from '@/components/AdInjector';
import ShareWhatsApp from '@/components/ShareWhatsApp';
import { getProxiedImageUrl } from '@/lib/image';
import type { Article } from '@/types/article';

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

export default function ArticleDetailView({ article }: { article: Article }) {
  const metadata = normalizeMetadata(article.metadata);
  const originalSource = (metadata.original_source as string | undefined) || 'Agencia de Noticias';
  const originalUrl = article.external_url || (metadata.original_url as string | undefined);
  const transformedAt = metadata.transformed_at
    ? new Date(String(metadata.transformed_at)).toLocaleDateString('es-CL')
    : null;

  const rawContent = article.content || '';

  /**
   * Si el contenido no tiene etiquetas HTML, es texto plano:
   * convertimos doble-salto-de-línea → párrafos y salto simple → <br>.
   */
  function toHtml(text: string): string {
    const hasHtml = /<[a-z][\s\S]*>/i.test(text);
    if (hasHtml) return text;
    return text
      .split(/\n{2,}/)
      .map((block) => `<p>${block.replace(/\n/g, '<br>')}</p>`)
      .join('');
  }

  const contentWithAds = injectAds(toHtml(rawContent));
  const processedContent = contentWithAds.replace(
    '<div data-native-ad="true"></div>',
    '<div id="native-ad-placeholder"></div>'
  );

  const published = article.published_at
    ? new Date(article.published_at)
    : null;
  const dateValid = published && !Number.isNaN(published.getTime());
  const dateDisplay = dateValid
    ? published!.toLocaleDateString('es-CL', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      })
    : '';
  const dateTimeAttr = dateValid ? published!.toISOString() : undefined;

  return (
    <div className="min-h-screen bg-gray-50">
      <article className="max-w-4xl mx-auto px-4 py-8">
        <header className="mb-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">{article.title}</h1>
          <div className="flex flex-wrap items-center gap-4 text-sm text-gray-600">
            {dateDisplay && (
              <time dateTime={dateTimeAttr}>{dateDisplay}</time>
            )}
            {dateDisplay && <span>•</span>}
            <span className="flex items-center gap-1">
              <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                <path
                  fillRule="evenodd"
                  d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                  clipRule="evenodd"
                />
              </svg>
              {originalSource}
            </span>
            {transformedAt && (
              <>
                <span>•</span>
                <span className="text-green-600 text-xs">✓ Transformada por IA</span>
              </>
            )}
          </div>
        </header>

        <div className="mb-8 relative w-full">
          <img
            src={getProxiedImageUrl(article.image_url, {
              title: article.title,
              slug: article.slug,
            })}
            alt={article.title}
            className="w-full h-auto rounded-lg shadow-lg"
            style={{ display: 'block' }}
          />
          <div className="absolute bottom-3 right-3">
            <AdminImageEditLink articleId={String(article.id)} />
          </div>
        </div>

        <div
          dangerouslySetInnerHTML={{ __html: processedContent }}
          className="article-content"
        />

        <AdInjector content={processedContent} />

        {originalUrl && (
          <div className="mt-8 p-6 bg-gray-100 rounded-lg border-l-4 border-blue-500">
            <h3 className="text-lg font-semibold text-gray-800 mb-2">📰 Información de la Noticia</h3>
            <p className="text-gray-600 mb-4">
              Esta noticia ha sido recopilada de medios regionales para mantener informados a lectores de la Zona Sur.
            </p>
            <div className="flex flex-wrap items-center gap-4">
              <span className="text-sm text-gray-500">
                <strong>Fuente:</strong> {originalSource}
              </span>
              {dateDisplay && (
                <span className="text-sm text-gray-500">
                  <strong>Publicado:</strong> {dateDisplay}
                </span>
              )}
              {transformedAt && (
                <span className="text-sm text-green-600">
                  <strong>✓ Contenido transformado:</strong> {transformedAt}
                </span>
              )}
            </div>
            <div className="mt-4">
              <a
                href={originalUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium"
              >
                Leer noticia original en {originalSource}
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                  />
                </svg>
              </a>
            </div>
          </div>
        )}

        <div className="mt-12 flex items-center justify-between border-t pt-8">
          <div className="flex gap-4">
            <ShareWhatsApp title={article.title} article={article} />
          </div>

          {originalUrl && (
            <a
              href={originalUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2"
            >
              Ver Original
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                />
              </svg>
            </a>
          )}
        </div>
      </article>
    </div>
  );
}
