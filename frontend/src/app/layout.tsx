import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: {
    default: "Diario Zona Sur - Noticias del sur de Chile",
    template: "%s | Diario Zona Sur",
  },
  description: "Noticias de la Zona Sur: Araucanía, Malleco, Angol, Victoria, Collipulli y la región.",
  keywords: ["Zona Sur", "Araucanía", "Chile", "noticias", "Angol", "Victoria", "Collipulli", "Malleco", "actualidad"],
  authors: [{ name: "Diario Zona Sur" }],
  creator: "Diario Zona Sur",
  publisher: "Diario Zona Sur",
  metadataBase: new URL('https://diariozonasur.cl'),
  alternates: {
    canonical: '/',
  },
  openGraph: {
    title: "Diario Zona Sur",
    description: "Noticias del sur de Chile · Araucanía y región",
    url: 'https://diariozonasur.cl',
    siteName: 'Diario Zona Sur',
    locale: 'es_CL',
    type: 'website',
    images: [
      {
        url: '/og-image.jpg',
        width: 1200,
        height: 630,
        alt: 'Diario Zona Sur',
      },
    ],
  },
  twitter: {
    card: 'summary_large_image',
    title: "Diario Zona Sur",
    description: "Noticias del sur de Chile · Araucanía y región",
    images: ['/og-image.jpg'],
  },
  robots: {
    index: true,
    follow: true,
    googleBot: {
      index: true,
      follow: true,
      'max-video-preview': -1,
      'max-image-preview': 'large',
      'max-snippet': -1,
    },
  },
  verification: {
    // Aquí puedes agregar verificaciones de Google, Bing, etc.
    // google: 'tu-codigo-de-verificacion',
  },
  category: 'news',
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="es">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="dns-prefetch" href="https://api.diariozonasur.cl" />
      </head>
      <body className="antialiased bg-gray-50">
        {children}
      </body>
    </html>
  );
}
