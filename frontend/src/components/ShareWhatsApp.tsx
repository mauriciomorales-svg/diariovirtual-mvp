'use client';

import { MessageCircle } from 'lucide-react';
import { publicArticlePath } from '@/lib/articleUrl';
import type { Article } from '@/types/article';

interface ShareWhatsAppProps {
  title: string;
  article: Article;
}

export default function ShareWhatsApp({ title, article }: ShareWhatsAppProps) {
  const handleShare = async () => {
    const origin = typeof window !== 'undefined' ? window.location.origin : '';
    const path = publicArticlePath(article);
    const articleUrl = path.startsWith('http') ? path : `${origin}${path}`;
    const text = `🚨 ${title} - ${articleUrl}`;

    if (typeof navigator !== 'undefined' && navigator.share) {
      try {
        await navigator.share({
          title: title,
          text: text,
          url: articleUrl,
        });
      } catch {
        // Usuario canceló
      }
    } else {
      const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
      window.open(whatsappUrl, '_blank');
    }
  };

  return (
    <button
      type="button"
      onClick={handleShare}
      className="bg-green-600 text-white p-2 rounded-lg hover:bg-green-700 transition-colors"
      title="Compartir por WhatsApp"
    >
      <MessageCircle size={20} />
    </button>
  );
}
