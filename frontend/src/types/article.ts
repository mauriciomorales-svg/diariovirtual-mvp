export interface Article {
  id: string;
  title: string;
  slug: string;
  source_hash: string;
  excerpt: string;
  image_url: string;
  is_external: boolean;
  external_url?: string;
  status: string;
  published_at: string;
  content?: string;
}
