import { Link } from '@inertiajs/react';
import { useTranslate } from '@/helpers';

export default function Hero() {
  const { __ } = useTranslate();
  return (
    <section className="relative h-[600px] overflow-hidden bg-gray-900">
      <img
        src="https://picsum.photos/seed/gehero/1920/1080"
        alt="Future of Healthcare"
        className="absolute inset-0 w-full h-full object-cover opacity-60"
      />
      <div className="absolute inset-0 bg-gradient-to-r from-gray-900/80 to-transparent"></div>
      <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-center text-white">
        <h1 className="text-4xl md:text-6xl font-bold max-w-2xl mb-6 leading-tight">
          {__('Intelligent Healthcare for a :highlight', { highlight: '' })}<span className="text-blue-400">{__('Sustainable World')}</span>
        </h1>
        <p className="text-lg md:text-xl text-gray-200 max-w-xl mb-8">
          {__('VSENS Medical is an independent medtech company that builds a world that works. We are focused on personalized, connected healthcare solutions.')}
        </p>
        <div className="flex flex-col sm:flex-row gap-4">
          <Link href="/products" className="bg-ge-blue text-white px-8 py-4 rounded font-bold text-center hover:bg-blue-800 transition-colors uppercase tracking-widest text-sm">
            {__('Explore Products')}
          </Link>
          <Link href="/solutions" className="bg-white text-gray-900 px-8 py-4 rounded font-bold text-center hover:bg-gray-100 transition-colors uppercase tracking-widest text-sm">
            {__('Clinical Solutions')}
          </Link>
        </div>
      </div>
    </section>
  );
}
