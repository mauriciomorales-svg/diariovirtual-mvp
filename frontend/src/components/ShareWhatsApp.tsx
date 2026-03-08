'use client';

import { MessageCircle } from 'lucide-react';

interface ShareWhatsAppProps {
  title: string;
  slug: string;
}

export default function ShareWhatsApp({ title, slug }: ShareWhatsAppProps) {
  const handleShare = async () => {
    // Construir URL completa del artículo
    const origin = typeof window !== 'undefined' ? window.location.origin : '';
    const articleUrl = `${origin}/noticias/${slug}`;
    const text = `🚨 ${title} - ${articleUrl}`;
    
    if (typeof navigator !== 'undefined' && navigator.share) {
      try {
        await navigator.share({
          title: title,
          text: text,
          url: articleUrl,
        });
      } catch (error) {
        // User cancelled share
      }
    } else {
      // Fallback to WhatsApp Web
      const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
      window.open(whatsappUrl, '_blank');
    }
  };

  return (
    <button
      onClick={handleShare}
      className="bg-green-600 text-white p-2 rounded-lg hover:bg-green-700 transition-colors"
      title="Compartir por WhatsApp"
    >
      <MessageCircle size={20} />
    </button>
  );
}
