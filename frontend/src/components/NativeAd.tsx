'use client';

export default function NativeAd() {
  return (
    <div className="bg-gradient-to-r from-purple-600 to-blue-600 text-white p-6 rounded-lg my-6 shadow-lg">
      <div className="flex items-center justify-between">
        <div>
          <h3 className="text-xl font-bold mb-2">🛒 Donde Morales - Delivery Gratis</h3>
          <p className="text-sm opacity-90">Pedidos por WhatsApp con despacho inmediato en Renaico.</p>
          <button 
            className="mt-3 bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-colors"
            onClick={() => window.open('https://wa.me/56938938614?text=Hola+quiero+hacer+un+pedido', '_blank')}
          >
            Hacer Pedido
          </button>
        </div>
        <div className="text-4xl">🛍️</div>
      </div>
    </div>
  );
}
