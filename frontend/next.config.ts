import type { NextConfig } from "next";
import path from "path";

const nextConfig: NextConfig = {
  turbopack: {
    root: path.join(__dirname),
  },
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'via.placeholder.com',
        port: '',
        pathname: '/**',
      },
      {
        protocol: 'http',
        hostname: 'localhost',
        port: '8000',
        pathname: '/**',
      },
      {
        protocol: 'http',
        hostname: '127.0.0.1',
        port: '8000',
        pathname: '/**',
      },
    ],
  },
  async rewrites() {
    // En local: define NEXT_PUBLIC_API_URL=http://127.0.0.1:8000 en .env.local
    const backend =
      process.env.NEXT_PUBLIC_API_URL?.replace(/\/$/, '') ||
      'https://api.diariozonasur.cl';
    return [
      {
        source: '/api/articles',
        destination: `${backend}/api/v1/articles.php`,
      },
      {
        source: '/api/articles/:slug',
        destination: `${backend}/api/v1/articles.php?slug=:slug`,
      },
      {
        source: '/img/placeholder-img',
        destination: `${backend}/placeholder-img`,
      },
      {
        source: '/img/placeholder.svg',
        destination: `${backend}/placeholder.svg`,
      },
      {
        source: '/img/images/:path*',
        destination: `${backend}/images/:path*`,
      },
      {
        source: '/img/proxy/:path*',
        destination: `${backend}/api/v1/image-proxy/:path*`,
      },
    ];
  },
};

export default nextConfig;
