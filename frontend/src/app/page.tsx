import { getArticles } from '@/lib/api-simple';
import ArticleCard from '@/components/ArticleCard';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import { Article } from '@/types/article';

export const revalidate = 60;

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

  // Debug logging
  console.log('Articles loaded:', articles.length);
  console.log('Error:', error);
  console.log('Total:', total);

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
              <p className="font-bold">Error:</p>
              <p>{error}</p>
              <p className="text-sm mt-2">Verifica que el backend esté corriendo en http://localhost:8000</p>
            </div>
          )}

          {/* No Articles Message */}
          {articles.length === 0 && !error && (
            <div className="text-center py-12 bg-white rounded-lg shadow">
              <p className="text-gray-500 text-lg">No hay noticias disponibles</p>
              <p className="text-gray-400 mt-2">Usa el panel de Gemini para agregar noticias</p>
              <a 
                href="http://localhost:8000/dev/gemini/enhanced" 
                className="inline-block mt-4 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium"
              >
                📝 Crear Noticia
              </a>
            </div>
          )}

          {articles.length > 0 && (
            <>
              {/* Stats Bar */}
              <div className="flex justify-between items-center mb-6 text-sm text-gray-600">
                <span>{showing}</span>
                <span className="text-red-600 font-medium">
                  {new Date().toLocaleDateString('es-CL', { weekday: 'long', day: 'numeric', month: 'long' })}
                </span>
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
                          src={featuredArticle.image_url}
                          alt={featuredArticle.title}
                          className="w-full h-full object-cover"
                        />
                        <div className="absolute top-4 left-4 bg-red-600 text-white px-3 py-1 text-sm font-bold uppercase">
                          Destacada
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
                            href={featuredArticle.is_external ? featuredArticle.external_url : `/${featuredArticle.slug}`}
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
