'use client';

import React, { useEffect } from 'react';
import NativeAd from './NativeAd';

interface AdInjectorProps {
  content: string;
}

export default function AdInjector({ content }: AdInjectorProps) {
  useEffect(() => {
    // Find and replace placeholder with actual NativeAd component
    const placeholder = document.getElementById('native-ad-placeholder');
    if (placeholder && !placeholder.hasChildNodes()) {
      const adContainer = document.createElement('div');
      placeholder.parentNode?.replaceChild(adContainer, placeholder);
      
      // Render NativeAd component
      import('react-dom/client').then(({ createRoot }) => {
        const root = createRoot(adContainer);
        root.render(<NativeAd />);
      });
    }
  }, [content]);

  return null;
}
