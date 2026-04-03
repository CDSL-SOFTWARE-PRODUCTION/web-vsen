import MainLayout from '@/Layouts/MainLayout';
import { Link } from '@inertiajs/react';
import { Product } from '@/types';
import { useState, useEffect } from 'react';
import QuoteModal from '@/Components/QuoteModal';
import { useTranslate } from '@/helpers';

interface Props {
    product: Product;
}

export default function Show({ product }: Props) {
  const { __ } = useTranslate();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [activeImage, setActiveImage] = useState(product?.image);

  useEffect(() => {
    setActiveImage(product?.images?.[0] || product?.image);
  }, [product]);

  if (!product) return (
    <MainLayout title={__('Not Found')}>
        <div className="py-20 text-center">
        <h2 className="text-2xl font-bold">{__('Product not found.')}</h2>
        <Link href="/products" className="text-ge-blue underline mt-4 inline-block">{__('Back to catalog')}</Link>
        </div>
    </MainLayout>
  );

  return (
    <MainLayout title={product.name}>
      <QuoteModal 
        isOpen={isModalOpen} 
        closeModal={() => setIsModalOpen(false)} 
        productId={product.id}
        productName={product.name}
      />

      <div className="bg-white">
        {/* Breadcrumb */}
        <div className="bg-gray-50 border-b border-gray-200">
          <div className="max-w-7xl mx-auto px-4 py-4 flex text-xs font-bold uppercase tracking-widest text-gray-400 gap-2">
            <Link href="/" className="hover:text-ge-blue">{__('Home')}</Link>
            <span>/</span>
            <Link href="/products" className="hover:text-ge-blue">{__('Products')}</Link>
            <span>/</span>
            <span className="text-ge-blue">{product.name}</span>
          </div>
        </div>

        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
            {/* Image Gallery */}
            <div className="space-y-4">
              <div className="rounded-2xl overflow-hidden bg-gray-100 shadow-inner">
                <img src={activeImage} alt={product.name} className="w-full h-auto object-cover aspect-[4/3] object-contain bg-white" />
              </div>
              {product.images && product.images.length > 1 && (
                <div className="grid grid-cols-4 gap-4">
                  {product.images.map((imgSrc, i) => (
                    <div 
                      key={i} 
                      onClick={() => setActiveImage(imgSrc)}
                      className={`aspect-square bg-white rounded-lg border overflow-hidden cursor-pointer transition-colors ${activeImage === imgSrc ? 'border-ge-blue ring-2 ring-ge-blue/50' : 'border-gray-200 hover:border-ge-blue'}`}
                    >
                      <img src={imgSrc} className={`w-full h-full object-contain ${activeImage === imgSrc ? 'opacity-100' : 'opacity-60 hover:opacity-100'}`} />
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Details */}
            <div>
              <span className="text-sm uppercase font-bold text-ge-blue tracking-widest mb-2 block">{product.category}</span>
              <h1 className="text-4xl font-bold text-gray-900 mb-6">{product.name}</h1>
              <div 
                className="text-lg text-gray-600 mb-8 leading-relaxed" 
                dangerouslySetInnerHTML={{ __html: product.description }} 
              />

              <div className="space-y-8 mb-10">
                <div>
                  <h3 className="text-xs uppercase font-bold text-gray-400 tracking-widest mb-4">{__('Key Features')}</h3>
                  <ul className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {product.features.map((feat, i) => (
                      <li key={i} className="flex items-start gap-3">
                        <div className="mt-1 w-5 h-5 rounded-full bg-blue-50 text-ge-blue flex items-center justify-center flex-shrink-0">
                          <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                          </svg>
                        </div>
                        <span className="text-sm text-gray-700 font-medium">{feat}</span>
                      </li>
                    ))}
                  </ul>
                </div>
              </div>

              <div className="flex flex-col sm:flex-row gap-4">
                <button 
                  onClick={() => setIsModalOpen(true)}
                  className="flex-1 bg-ge-blue text-white px-8 py-4 rounded font-bold uppercase tracking-widest text-sm hover:bg-blue-800 transition-colors"
                >
                  {__('Request Information')}
                </button>
              </div>

              <div className="mt-12 p-6 bg-blue-50 rounded-xl border border-blue-100">
                <h4 className="font-bold text-ge-blue mb-2 uppercase text-xs tracking-widest">{__('Expert Consultation')}</h4>
                <p className="text-sm text-gray-600 mb-4">{__('Need help choosing the right solution for your facility? Talk to our clinical specialists.')}</p>
                <button className="text-ge-blue font-bold text-sm uppercase tracking-widest flex items-center hover:gap-2 transition-all">
                  {__('Contact an Expert')}
                  <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </MainLayout>
  );
}
