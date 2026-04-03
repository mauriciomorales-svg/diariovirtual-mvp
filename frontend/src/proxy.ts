import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

const API = (process.env.NEXT_PUBLIC_API_URL || 'https://api.diariozonasur.cl').replace(/\/$/, '');

function hostnameLikeSlug(pathname: string): string | null {
  const seg = pathname.replace(/^\//, '').split('/')[0];
  if (!seg || !seg.includes('.')) return null;
  if (/\.(jpg|jpeg|png|gif|webp|svg|ico|css|js|json|xml|txt)$/i.test(seg)) return null;
  const parts = seg.split('.');
  if (parts.length < 2) return null;
  const tld = parts[parts.length - 1];
  if (tld.length < 2 || tld.length > 24 || !/^[a-z]+$/i.test(tld)) return null;
  return seg;
}

export async function proxy(request: NextRequest) {
  const pathname = request.nextUrl.pathname;
  const slug = hostnameLikeSlug(pathname);
  if (!slug) return NextResponse.next();

  try {
    const res = await fetch(`${API}/api/v1/articles/${encodeURIComponent(slug)}`, {
      headers: { Accept: 'application/json' },
      cache: 'no-store',
    });
    if (!res.ok) return NextResponse.next();
    const data = (await res.json()) as { id?: string };
    if (data?.id && typeof data.id === 'string') {
      return NextResponse.redirect(new URL(`/news/${data.id}`, request.url), 307);
    }
  } catch {
    /* sigue a [slug] */
  }
  return NextResponse.next();
}

export const config = {
  matcher: ['/((?!_next/static|_next/image|favicon.ico|img/).*)'],
};
