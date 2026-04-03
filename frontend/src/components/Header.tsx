'use client';

import Link from 'next/link';
import { useState } from 'react';

const ADMIN_URL = process.env.NEXT_PUBLIC_API_URL
  ? process.env.NEXT_PUBLIC_API_URL.replace(/\/$/, '')
  : 'https://api.diariozonasur.cl';

export default function Header() {
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  const currentDate = new Date().toLocaleDateString('es-CL', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });

  const navItems = [
    { name: 'Inicio', href: '/' },
    { name: 'Angol', href: '/?categoria=angol' },
    { name: 'Victoria', href: '/?categoria=victoria' },
    { name: 'Collipulli', href: '/?categoria=collipulli' },
    { name: 'La Araucanía', href: '/?categoria=araucania' },
    { name: 'Nacional', href: '/?categoria=nacional' },
    { name: 'Admin', href: `${ADMIN_URL}/admin/dashboard`, external: true },
    { name: 'Crear Noticia', href: `${ADMIN_URL}/admin/articles/create`, external: true },
    { name: 'Traer Externas', href: `${ADMIN_URL}/admin/gemini/enhanced`, external: true },
  ];

  return (
    <header className="bg-white shadow-md">
      {/* Top Bar */}
      <div className="bg-red-700 text-white text-sm py-1">
        <div className="max-w-7xl mx-auto px-4 flex justify-between items-center">
          <span className="font-medium">{currentDate}</span>
          <div className="flex space-x-4">
            <span className="hidden sm:inline">Edición Digital</span>
            <span className="hidden sm:inline">|</span>
            <span className="hidden sm:inline">Clima: 18°C ☀️</span>
          </div>
        </div>
      </div>

      {/* Main Header */}
      <div className="max-w-7xl mx-auto px-4 py-6">
        <div className="flex flex-col md:flex-row justify-between items-center">
          {/* Logo */}
          <Link href="/" className="text-center md:text-left mb-4 md:mb-0">
            <h1 className="text-3xl md:text-4xl font-black text-red-700 tracking-tight leading-tight">
              Diario Zona Sur
            </h1>
            <p className="text-sm md:text-base text-gray-600 font-medium mt-1">
              Noticias · Araucanía y sur de Chile
            </p>
          </Link>

          {/* Search & Social */}
          <div className="flex flex-col items-center md:items-end space-y-3">
            <div className="flex space-x-4">
              <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:text-blue-800 text-2xl">
                <span>📘</span>
              </a>
              <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" className="text-sky-500 hover:text-sky-700 text-2xl">
                <span>🐦</span>
              </a>
              <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" className="text-pink-600 hover:text-pink-800 text-2xl">
                <span>📸</span>
              </a>
              <a href="https://wa.me" target="_blank" rel="noopener noreferrer" className="text-green-600 hover:text-green-800 text-2xl">
                <span>💬</span>
              </a>
            </div>
            <div className="relative">
              <input
                type="text"
                placeholder="Buscar noticias..."
                className="pl-4 pr-10 py-2 border border-gray-300 rounded-full w-64 focus:outline-none focus:ring-2 focus:ring-red-500"
              />
              <button className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                🔍
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Navigation */}
      <nav className="bg-red-700 text-white">
        <div className="max-w-7xl mx-auto px-4">
          <div className="flex justify-between items-center md:hidden py-3">
            <span className="font-bold">Menú</span>
            <button
              onClick={() => setIsMenuOpen(!isMenuOpen)}
              className="text-white focus:outline-none"
            >
              {isMenuOpen ? '✕' : '☰'}
            </button>
          </div>

          <ul className={`${isMenuOpen ? 'block' : 'hidden'} md:flex md:space-x-1 py-0 md:py-0`}>
            {navItems.map((item) => (
              <li key={item.name}>
                {'external' in item && item.external ? (
                  <a
                    href={item.href}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="block px-4 py-3 hover:bg-red-800 transition-colors font-medium text-sm uppercase tracking-wide"
                  >
                    {item.name}
                  </a>
                ) : (
                  <Link
                    href={item.href}
                    className="block px-4 py-3 hover:bg-red-800 transition-colors font-medium text-sm uppercase tracking-wide"
                  >
                    {item.name}
                  </Link>
                )}
              </li>
            ))}
          </ul>
        </div>
      </nav>
    </header>
  );
}
