import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "🚨 El Diario de Malleco - Noticias de la Provincia",
  description: "Noticias locales de Angol, Victoria, Collipulli y toda la Provincia de Malleco",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="es">
      <body className="antialiased bg-gray-50">
        {children}
      </body>
    </html>
  );
}
