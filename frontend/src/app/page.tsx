import { getArticles } from '@/lib/api-simple';
import { publicArticlePath } from '@/lib/articleUrl';
import AdminImageEditLink from '@/components/AdminImageEditLink';
import ArticleCard from '@/components/ArticleCard';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import RefreshButton from '@/components/RefreshButton';
import { getProxiedImageUrl } from '@/lib/image';
import { Article } from '@/types/article';

export const dynamic = 'force-dynamic';

export default async function HomePage() {
  let articles: Article[] = [];
  let total = 0;
  let showing = '';
  let error: string | null = null;

  try {
    const result = await getArticles();
    articles = result.articles;
    total = result.total;
    showing = result.showing;
  } catch (e) {
    error = e instanceof Error ? e.message : 'Error al cargar noticias';
  }

  // Separar noticia destacada (la primera) del resto
  const featuredArticle = articles[0];
  const otherArticles = articles.slice(1);

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <Header />

      <main className="flex-grow">
        {/* Breaking News Banner */}
        <div className="bg-red-600 text-white py-2">
          <div className="max-w-7xl mx-auto px-4 flex items-center">
            <span className="bg-white text-red-600 px-2 py-1 text-xs font-bold uppercase mr-3">
              Última Hora
            </span>
            <span className="text-sm truncate">
              {featuredArticle ? featuredArticle.title : 'Cargando noticias...'}
            </span>
          </div>
        </div>

        <div className="max-w-7xl mx-auto px-4 py-8">
          {/* Error Message */}
          {error && (
            <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
              <p className="font-bold">No pudimos cargar las noticias</p>
              <p className="text-sm mt-1">Intenta de nuevo en unos minutos.</p>
            </div>
          )}

          {/* No Articles Message */}
          {articles.length === 0 && !error && (
            <div className="text-center py-12 bg-white rounded-lg shadow">
              <p className="text-gray-500 text-lg">No hay noticias publicadas por ahora</p>
              <p className="text-gray-400 mt-2 max-w-md mx-auto">
                Vuelve pronto: aquí verás las últimas noticias de la Zona Sur.
              </p>
            </div>
          )}

          {articles.length > 0 && (
            <>
              {/* Stats Bar */}
              <div className="flex justify-between items-center mb-6 text-sm text-gray-600 flex-wrap gap-2">
                <span>{showing}</span>
                <div className="flex items-center gap-3">
                  <RefreshButton />
                  <span className="text-red-600 font-medium">
                    {new Date().toLocaleDateString('es-CL', { weekday: 'long', day: 'numeric', month: 'long' })}
                  </span>
                </div>
              </div>

              {/* Featured Article */}
              {featuredArticle && (
                <section className="mb-10">
                  <h2 className="text-2xl font-bold text-gray-900 mb-4 border-l-4 border-red-600 pl-3">
                    Noticia Destacada
                  </h2>
                  <article className="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div className="md:flex">
                      <div className="md:w-2/3 relative h-64 md:h-96">
                        <img
                          src={getProxiedImageUrl(featuredArticle.image_url, { title: featuredArticle.title, slug: featuredArticle.slug })}
                          alt={featuredArticle.title}
                          className="w-full h-full object-cover"
                        />
                        <div className="absolute top-4 left-4 bg-red-600 text-white px-3 py-1 text-sm font-bold uppercase">
                          Destacada
                        </div>
                        <div className="absolute bottom-3 right-3">
                          <AdminImageEditLink articleId={featuredArticle.id} />
                        </div>
                      </div>
                      <div className="md:w-1/3 p-6 flex flex-col justify-center">
                        <span className="text-red-600 text-sm font-semibold uppercase tracking-wide mb-2">
                          {featuredArticle.is_external ? 'Noticia Externa' : 'Local'}
                        </span>
                        <h3 className="text-2xl font-bold text-gray-900 mb-3 leading-tight">
                          {featuredArticle.title}
                        </h3>
                        <p className="text-gray-600 mb-4 line-clamp-3">
                          {featuredArticle.excerpt}
                        </p>
                        <div className="flex items-center justify-between mt-auto">
                          <span className="text-sm text-gray-500">
                            {new Date(featuredArticle.published_at).toLocaleDateString('es-CL')}
                          </span>
                          <a
                            href={publicArticlePath(featuredArticle)}
                            target={featuredArticle.is_external ? '_blank' : undefined}
                            rel={featuredArticle.is_external ? 'noopener noreferrer' : undefined}
                            className="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition font-medium"
                          >
                            Leer más →
                          </a>
                        </div>
                      </div>
                    </div>
                  </article>
                </section>
              )}

              {/* Latest News Grid */}
              <section>
                <h2 className="text-2xl font-bold text-gray-900 mb-6 border-l-4 border-red-600 pl-3">
                  Últimas Noticias
                </h2>
                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                  {otherArticles.map((article) => (
                    <ArticleCard key={article.id} article={article} />
                  ))}
                </div>
              </section>

              {/* Load More Button */}
              {otherArticles.length >= 99 && (
                <div className="text-center mt-10">
                  <button className="px-8 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition">
                    Cargar más noticias
                  </button>
                </div>
              )}
            </>
          )}
        </div>
      </main>

      <Footer />
    </div>
  );
}
