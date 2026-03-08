import Link from 'next/link';

export default function Footer() {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-gray-900 text-white">
      {/* Main Footer */}
      <div className="max-w-7xl mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* Brand */}
          <div className="col-span-1 md:col-span-1">
            <h3 className="text-2xl font-black text-red-500 mb-4">🚨 EL DIARIO</h3>
            <p className="text-gray-400 text-sm mb-4">
              Tu fuente de noticias locales de la Provincia de Malleco. 
              Angol, Victoria, Collipulli y toda la región.
            </p>
            <div className="flex space-x-3">
              <a href="#" className="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center hover:bg-blue-700 transition">
                <span className="text-sm">f</span>
              </a>
              <a href="#" className="w-8 h-8 bg-sky-500 rounded-full flex items-center justify-center hover:bg-sky-600 transition">
                <span className="text-sm">t</span>
              </a>
              <a href="#" className="w-8 h-8 bg-pink-600 rounded-full flex items-center justify-center hover:bg-pink-700 transition">
                <span className="text-sm">i</span>
              </a>
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="text-lg font-semibold mb-4 text-red-400">Secciones</h4>
            <ul className="space-y-2 text-sm text-gray-400">
              <li><Link href="/" className="hover:text-white transition">Inicio</Link></li>
              <li><Link href="/categoria/angol" className="hover:text-white transition">Angol</Link></li>
              <li><Link href="/categoria/victoria" className="hover:text-white transition">Victoria</Link></li>
              <li><Link href="/categoria/collipulli" className="hover:text-white transition">Collipulli</Link></li>
              <li><Link href="/categoria/araucania" className="hover:text-white transition">La Araucanía</Link></li>
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h4 className="text-lg font-semibold mb-4 text-red-400">Contacto</h4>
            <ul className="space-y-2 text-sm text-gray-400">
              <li>📍 Angol, Chile</li>
              <li>📞 +56 9 1234 5678</li>
              <li>✉️ contacto@diariomalleco.cl</li>
              <li>🕐 Lunes a Viernes: 8:00 - 18:00</li>
            </ul>
          </div>

          {/* Newsletter */}
          <div>
            <h4 className="text-lg font-semibold mb-4 text-red-400">Newsletter</h4>
            <p className="text-sm text-gray-400 mb-3">
              Recibe las noticias más importantes en tu correo.
            </p>
            <form className="flex flex-col space-y-2">
              <input
                type="email"
                placeholder="Tu correo electrónico"
                className="px-3 py-2 bg-gray-800 border border-gray-700 rounded text-sm focus:outline-none focus:border-red-500"
              />
              <button
                type="submit"
                className="px-3 py-2 bg-red-600 hover:bg-red-700 rounded text-sm font-medium transition"
              >
                Suscribirse
              </button>
            </form>
          </div>
        </div>
      </div>

      {/* Bottom Bar */}
      <div className="bg-gray-950 border-t border-gray-800">
        <div className="max-w-7xl mx-auto px-4 py-4">
          <div className="flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
            <p>© {currentYear} El Diario de Malleco. Todos los derechos reservados.</p>
            <div className="flex space-x-4 mt-2 md:mt-0">
              <Link href="/privacidad" className="hover:text-white transition">Política de Privacidad</Link>
              <Link href="/terminos" className="hover:text-white transition">Términos de Uso</Link>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}
