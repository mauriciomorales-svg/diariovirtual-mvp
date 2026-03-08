import type { NextConfig } from "next";

const nextConfig: NextConfig = {
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
    ],
  },
  async rewrites() {
    return [
      {
        source: '/api/articles',
        destination: 'http://localhost:8000/api/v1/articles.php',
      },
      {
        source: '/api/articles/:slug',
        destination: 'http://localhost:8000/api/v1/articles.php?slug=:slug',
      },
    ];
  },
};

export default nextConfig;
