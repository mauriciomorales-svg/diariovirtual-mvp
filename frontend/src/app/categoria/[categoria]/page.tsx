import { getArticles } from '@/lib/api-simple';
import { filterArticlesByCategoria, categoriaLabel } from '@/lib/articleFilter';
import ArticleCard from '@/components/ArticleCard';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import RefreshButton from '@/components/RefreshButton';
import Link from 'next/link';
import { Article } from '@/types/article';

export const dynamic = 'force-dynamic';

export default async function CategoriaPage({
  params,
}: {
  params: Promise<{ categoria: string }>;
}) {
  const { categoria } = await params;
  const label = categoriaLabel(categoria);

  let articles: Article[] = [];
  let error: string | null = null;

  try {
    const result = await getArticles();
    articles = filterArticlesByCategoria(result.articles, categoria);
  } catch (e) {
    error = e instanceof Error ? e.message : 'Error al cargar noticias';
  }

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <Header />

      <main className="flex-grow max-w-7xl mx-auto px-4 py-8 w-full">
        <div className="mb-6">
          <Link href="/" className="text-red-600 hover:underline text-sm">
            ← Inicio
          </Link>
          <h1 className="text-3xl font-bold text-gray-900 mt-2 border-l-4 border-red-600 pl-3">
            Noticias de {label}
          </h1>
          <p className="text-gray-600 mt-2">
            {articles.length} noticia(s) en Diario Zona Sur
          </p>
        </div>

        {error && (
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {error}
          </div>
        )}

        {articles.length === 0 && !error && (
          <div className="text-center py-12 bg-white rounded-lg shadow">
            <p className="text-gray-500">No hay noticias de {label} por ahora.</p>
            <Link href="/" className="text-red-600 mt-4 inline-block hover:underline">
              Ver todas las noticias
            </Link>
          </div>
        )}

        {articles.length > 0 && (
          <>
            <div className="flex justify-end mb-4">
              <RefreshButton />
            </div>
            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
              {articles.map((article) => (
                <ArticleCard key={article.id} article={article} />
              ))}
            </div>
          </>
        )}
      </main>

      <Footer />
    </div>
  );
}
