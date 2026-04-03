import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import QuoteModal from './QuoteModal';
import { useTranslate } from '@/helpers';
import { PageProps } from '@/types';

export default function Header() {
    const { __ } = useTranslate();
    const { props } = usePage<PageProps>();
    const { locale, categories } = props;
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

    const handleSearch = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter') {
            router.get('/products', { q: searchQuery });
        }
    };

    return (
        <header className="sticky top-0 z-40 shadow-sm font-sans">
            <QuoteModal isOpen={isModalOpen} closeModal={() => setIsModalOpen(false)} />

            {/* Top Bar: Search & Language - Gray Background */}
            <div className="bg-gray-100 border-b border-gray-200">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-10 flex justify-end items-center gap-6">
                    {/* Search - Hidden on mobile */}
                    <div className="relative hidden md:block">
                        <input
                            type="text"
                            placeholder={__('Search...')}
                            className="w-64 bg-white pl-10 pr-4 py-2 border border-gray-300 rounded-full text-sm focus:outline-none focus:border-ge-blue focus:ring-1 focus:ring-ge-blue transition-all"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            onKeyDown={handleSearch}
                        />
                        <svg className="w-4 h-4 text-gray-400 absolute left-3.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    {/* Language Switcher */}
                    <div className="flex items-center gap-2 text-[11px] font-bold tracking-wide">
                        <Link
                            href="/language/en"
                            className={`${locale === 'en' ? 'text-ge-blue' : 'text-gray-500 hover:text-gray-700'}`}
                        >
                            EN
                        </Link>
                        <span className="text-gray-300">|</span>
                        <Link
                            href="/language/vi"
                            className={`${locale === 'vi' ? 'text-ge-blue' : 'text-gray-500 hover:text-gray-700'}`}
                        >
                            VN
                        </Link>
                    </div>
                </div>
            </div>

            {/* Main Navigation: Logo, Menu, CTA - White Background */}
            <div className="bg-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center h-20">
                        {/* Logo */}
                        <div className="flex-shrink-0 flex items-center">
                            <Link href="/" className="flex items-center gap-2">
                                <span className="text-3xl font-bold text-ge-blue italic tracking-tighter">VSENS</span>
                                <span className="text-sm font-semibold text-gray-500 uppercase tracking-widest mt-1">Medical</span>
                            </Link>
                        </div>

                        {/* Navigation Links - Desktop */}
                        <nav className="hidden lg:flex items-center space-x-1">
                            {/* Products Dropdown */}
                            <div className="group relative">
                                <Link
                                    href="/products"
                                    className={`${usePage().url.startsWith('/products') ? 'text-ge-blue' : 'text-gray-700'} group-hover:text-ge-blue px-4 py-3 rounded-md text-sm font-bold uppercase tracking-wide transition-colors flex items-center`}
                                >
                                    {__('Products')}
                                    <svg className={`w-3 h-3 ml-1 ${usePage().url.startsWith('/products') ? 'text-ge-blue' : 'text-gray-400'} group-hover:text-ge-blue transition-colors`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                    </svg>
                                </Link>

                                {/* Dropdown Menu */}
                                <div className="absolute left-0 pt-2 w-56 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-in-out transform group-hover:translate-y-0 translate-y-2 z-50">
                                    <div className="bg-white rounded-lg shadow-xl border border-gray-100 overflow-hidden ring-1 ring-black ring-opacity-5">
                                        <div className="py-2">
                                            <Link
                                                href="/products"
                                                className="block px-4 py-2 text-sm text-ge-blue font-bold hover:bg-gray-50 border-b border-gray-100"
                                            >
                                                {__('All Products')}
                                            </Link>
                                            {categories?.map((category) => (
                                                <Link
                                                    key={category.slug}
                                                    href={`/products?category=${encodeURIComponent(category.name)}`}
                                                    className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-ge-blue transition-colors"
                                                >
                                                    {category.name}
                                                </Link>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <Link
                                href="/services"
                                className={`${usePage().url.startsWith('/services') ? 'text-ge-blue' : 'text-gray-700'} hover:text-ge-blue px-4 py-3 rounded-md text-sm font-bold uppercase tracking-wide transition-colors`}
                            >
                                {__('Services')}
                            </Link>
                            <Link
                                href="/solutions"
                                className={`${usePage().url.startsWith('/solutions') ? 'text-ge-blue' : 'text-gray-700'} hover:text-ge-blue px-4 py-3 rounded-md text-sm font-bold uppercase tracking-wide transition-colors`}
                            >
                                {__('Solutions')}
                            </Link>
                            <Link
                                href="/education"
                                className={`${usePage().url.startsWith('/education') ? 'text-ge-blue' : 'text-gray-700'} hover:text-ge-blue px-4 py-3 rounded-md text-sm font-bold uppercase tracking-wide transition-colors`}
                            >
                                {__('Education')}
                            </Link>
                            <Link
                                href="/insights/news"
                                className={`${usePage().url.startsWith('/insights') ? 'text-ge-blue' : 'text-gray-700'} hover:text-ge-blue px-4 py-3 rounded-md text-sm font-bold uppercase tracking-wide transition-colors`}
                            >
                                {__('Insights')}
                            </Link>
                        </nav>

                        {/* Right CTA & Mobile Menu Button */}
                        <div className="flex items-center gap-4">
                            {/* Contact Button - Hidden on mobile, shown in mobile menu */}
                            <Link
                                href="/contact"
                                className="hidden lg:block bg-ge-blue text-white px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-widest hover:bg-blue-800 transition-all shadow-sm hover:shadow-md"
                            >
                                {__('Contact Us')}
                            </Link>

                            {/* Mobile Menu Button */}
                            <button
                                onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
                                className="lg:hidden p-2 rounded-md text-gray-700 hover:text-ge-blue hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-ge-blue focus:ring-offset-2"
                                aria-label="Toggle menu"
                                aria-expanded={isMobileMenuOpen}
                            >
                                {isMobileMenuOpen ? (
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                ) : (
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                )}
                            </button>
                        </div>
                    </div>

                    {/* Mobile Menu Dropdown */}
                    <div className={`lg:hidden border-t border-gray-200 bg-white transition-all duration-300 ease-in-out ${isMobileMenuOpen
                            ? 'max-h-[calc(100vh-140px)] opacity-100 overflow-y-auto overscroll-contain'
                            : 'max-h-0 opacity-0 overflow-hidden'
                        }`}
                        style={isMobileMenuOpen ? {
                            WebkitOverflowScrolling: 'touch',
                            scrollBehavior: 'smooth'
                        } : {}}
                    >
                        <div className="px-4 py-4 space-y-1 pb-6 min-h-full">
                            {/* Mobile Search */}
                            <div className="relative mb-4">
                                <input
                                    type="text"
                                    placeholder={__('Search...')}
                                    className="w-full bg-gray-50 pl-10 pr-4 py-2.5 border border-gray-300 rounded-full text-sm focus:outline-none focus:border-ge-blue focus:ring-1 focus:ring-ge-blue transition-all"
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    onKeyDown={handleSearch}
                                />
                                <svg className="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            {/* Products with Categories */}
                            <div className="space-y-1">
                                <Link
                                    href="/products"
                                    onClick={() => setIsMobileMenuOpen(false)}
                                    className={`block px-4 py-3 rounded-md text-sm font-bold uppercase tracking-wide transition-colors ${usePage().url.startsWith('/products')
                                            ? 'text-ge-blue bg-blue-50'
                                            : 'text-gray-700 hover:text-ge-blue hover:bg-gray-50'
                                        }`}
                                >
                                    {__('Products')}
                                </Link>
                                {categories && categories.length > 0 && (
                                    <div className="pl-4 space-y-1 border-l-2 border-gray-100 ml-4">
                                        <Link
                                            href="/products"
                                            onClick={() => setIsMobileMenuOpen(false)}
                                            className="block px-4 py-2 text-sm text-ge-blue font-semibold hover:bg-gray-50 rounded-md"
                                        >
                                            {__('All Products')}
                                        </Link>
                                        {categories.map((category) => (
                                            <Link
                                                key={category.slug}
                                                href={`/products?category=${encodeURIComponent(category.name)}`}
                                                onClick={() => setIsMobileMenuOpen(false)}
                                                className="block px-4 py-2 text-sm text-gray-600 hover:text-ge-blue hover:bg-gray-50 rounded-md transition-colors"
                                            >
                                                {category.name}
                                            </Link>
                                        ))}
                                    </div>
                                )}
                            </div>

                            {/* Other Navigation Links */}
                            <Link
                                href="/services"
                                onClick={() => setIsMobileMenuOpen(false)}
                                className={`block px-4 py-3 rounded-md text-sm font-bold uppercase tracking-wide transition-colors ${usePage().url.startsWith('/services')
                                        ? 'text-ge-blue bg-blue-50'
                                        : 'text-gray-700 hover:text-ge-blue hover:bg-gray-50'
                                    }`}
                            >
                                {__('Services')}
                            </Link>
                            <Link
                                href="/solutions"
                                onClick={() => setIsMobileMenuOpen(false)}
                                className={`block px-4 py-3 rounded-md text-sm font-bold uppercase tracking-wide transition-colors ${usePage().url.startsWith('/solutions')
                                        ? 'text-ge-blue bg-blue-50'
                                        : 'text-gray-700 hover:text-ge-blue hover:bg-gray-50'
                                    }`}
                            >
                                {__('Solutions')}
                            </Link>
                            <Link
                                href="/education"
                                onClick={() => setIsMobileMenuOpen(false)}
                                className={`block px-4 py-3 rounded-md text-sm font-bold uppercase tracking-wide transition-colors ${usePage().url.startsWith('/education')
                                        ? 'text-ge-blue bg-blue-50'
                                        : 'text-gray-700 hover:text-ge-blue hover:bg-gray-50'
                                    }`}
                            >
                                {__('Education')}
                            </Link>
                            <Link
                                href="/insights/news"
                                onClick={() => setIsMobileMenuOpen(false)}
                                className={`block px-4 py-3 rounded-md text-sm font-bold uppercase tracking-wide transition-colors ${usePage().url.startsWith('/insights')
                                        ? 'text-ge-blue bg-blue-50'
                                        : 'text-gray-700 hover:text-ge-blue hover:bg-gray-50'
                                    }`}
                            >
                                {__('Insights')}
                            </Link>

                            {/* Contact Button for Mobile */}
                            <Link
                                href="/contact"
                                onClick={() => setIsMobileMenuOpen(false)}
                                className="block mt-4 bg-ge-blue text-white px-6 py-3 rounded-full text-xs font-bold uppercase tracking-widest hover:bg-blue-800 transition-all shadow-sm text-center"
                            >
                                {__('Contact Us')}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    );
}