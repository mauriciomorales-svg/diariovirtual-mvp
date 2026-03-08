'use client';

import React from 'react';
import NativeAd from './NativeAd';

interface GeminiContentProcessorProps {
  content: string;
  className?: string;
}

export default function GeminiContentProcessor({ content, className = '' }: GeminiContentProcessorProps) {
  // Procesa el contenido para reemplazar placeholders
  const processContent = (rawContent: string): string => {
    // Reemplazar [NATIVE_AD_PLACEHOLDER] con el componente de anuncio
    let processedContent = rawContent.replace(
      /\[NATIVE_AD_PLACEHOLDER\]/g,
      '<div id="native-ad-placeholder"></div>'
    );

    // Procesar párrafos cortos para mejor visualización
    processedContent = processedContent
      .split('\n\n')
      .map(paragraph => {
        const trimmed = paragraph.trim();
        if (trimmed.length > 0) {
          // Si es un párrafo muy corto, agregar clase especial
          if (trimmed.length < 100) {
            return `<p class="text-lg font-semibold text-gray-900 mb-4">${trimmed}</p>`;
          }
          return `<p class="text-gray-700 mb-4 leading-relaxed">${trimmed}</p>`;
        }
        return '';
      })
      .filter(p => p.length > 0)
      .join('\n');

    return processedContent;
  };

  // Renderiza el contenido con el anuncio inyectado
  const renderContentWithAds = () => {
    const processedContent = processContent(content);
    const parts = processedContent.split('<div id="native-ad-placeholder"></div>');
    
    return (
      <div className={className}>
        {parts.map((part, index) => (
          <React.Fragment key={index}>
            <div 
              dangerouslySetInnerHTML={{ __html: part }} 
              className="prose prose-lg max-w-none"
            />
            {index < parts.length - 1 && (
              <div className="my-8">
                <NativeAd />
              </div>
            )}
          </React.Fragment>
        ))}
      </div>
    );
  };

  return renderContentWithAds();
}
