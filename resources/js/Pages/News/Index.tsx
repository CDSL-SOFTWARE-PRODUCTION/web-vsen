import MainLayout from '@/Layouts/MainLayout';
import { Link } from '@inertiajs/react';
import { useTranslate } from '@/helpers';

interface Article {
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  image: string;
  category: string;
  published_at: string;
}

interface Props {
  articles: {
    data: Article[];
    links: any[];
  };
}

export default function Index({ articles }: Props) {
  const { __ } = useTranslate();

  return (
    <MainLayout title={__('Newsroom')}>
      <div className="bg-white">
        <div className="bg-gray-50 py-16 border-b border-gray-200">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 className="text-4xl font-bold text-gray-900 mb-4">{__('Newsroom')}</h1>
            <p className="text-lg text-gray-600 max-w-2xl">
              {__('Latest updates, press releases, and insights from VSENS Medical.')}
            </p>
          </div>
        </div>

        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            {articles.data.map(article => (
              <Link
                key={article.id}
                href={`/insights/news/${article.slug}`}
                className="group flex flex-col border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300"
              >
                <div className="h-48 overflow-hidden bg-gray-100">
                  {article.image ? (
                    <img
                      src={article.image}
                      alt={article.title}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    />
                  ) : (
                    <div className="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400">
                      {__('No Image')}
                    </div>
                  )}
                </div>
                <div className="p-6 flex-1 flex flex-col">
                  <div className="flex justify-between items-center mb-3">
                    <span className="text-[10px] uppercase font-bold text-ge-blue tracking-widest">{article.category}</span>
                    <span className="text-xs text-gray-400">{article.published_at}</span>
                  </div>
                  <h3 className="text-xl font-bold text-gray-900 mb-3 group-hover:text-ge-blue transition-colors line-clamp-2">{article.title}</h3>
                  <p className="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed flex-grow">{article.excerpt}</p>
                  <div className="mt-auto pt-4 flex items-center text-ge-blue font-bold text-xs uppercase tracking-widest">
                    {__('Read More')}
                    <svg className="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                  </div>
                </div>
              </Link>
            ))}
          </div>
        </div>
      </div>
    </MainLayout>
  );
}
