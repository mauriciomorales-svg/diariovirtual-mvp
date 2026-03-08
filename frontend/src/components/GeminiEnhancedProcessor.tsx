'use client';

import React, { useState, useEffect } from 'react';
import NativeAd from './NativeAd';

interface GeminiEnhancedProcessorProps {
  content: string;
  className?: string;
  onProcessingComplete?: (result: any) => void;
  onError?: (error: string) => void;
}

interface ProcessingState {
  isProcessing: boolean;
  progress: number;
  currentStep: string;
}

interface AnalyticsData {
  wordCount: number;
  readingTime: number;
  localFocus: string;
  sentiment: 'positive' | 'neutral' | 'negative';
  keywords: string[];
}

export default function GeminiEnhancedProcessor({ 
  content, 
  className = '', 
  onProcessingComplete,
  onError 
}: GeminiEnhancedProcessorProps) {
  const [processingState, setProcessingState] = useState<ProcessingState>({
    isProcessing: false,
    progress: 0,
    currentStep: ''
  });
  
  const [analytics, setAnalytics] = useState<AnalyticsData | null>(null);
  const [enhancedContent, setEnhancedContent] = useState<string>('');
  const [showAnalytics, setShowAnalytics] = useState(false);

  // Procesa el contenido con análisis avanzado
  const processContent = (rawContent: string): string => {
    let processedContent = rawContent;

    // Reemplazar placeholders de anuncios
    processedContent = processedContent.replace(
      /\[NATIVE_AD_PLACEHOLDER\]/g,
      '<div class="native-ad-wrapper" data-ad-type="gemini-enhanced"></div>'
    );

    // Procesamiento de párrafos con estilos mejorados
    processedContent = processedContent
      .split('\n\n')
      .map((paragraph, index) => {
        const trimmed = paragraph.trim();
        if (trimmed.length > 0) {
          // Detectar diferentes tipos de contenido
          if (trimmed.startsWith('🚨')) {
            // Título o encabezado
            return `<h2 class="text-2xl font-bold text-red-600 mb-4 fade-in">${trimmed}</h2>`;
          } else if (trimmed.length < 100) {
            // Párrafo corto - destacado
            return `<p class="text-lg font-semibold text-gray-900 mb-4 bg-blue-50 p-3 rounded-lg border-l-4 border-blue-500 fade-in">${trimmed}</p>`;
          } else if (trimmed.includes('http')) {
            // Párrafo con enlaces
            return `<p class="text-gray-700 mb-4 leading-relaxed fade-in">${processLinks(trimmed)}</p>`;
          } else {
            // Párrafo normal
            return `<p class="text-gray-700 mb-4 leading-relaxed fade-in">${trimmed}</p>`;
          }
        }
        return '';
      })
      .filter(p => p.length > 0)
      .join('\n');

    return processedContent;
  };

  // Procesa enlaces en el contenido
  const processLinks = (text: string): string => {
    const urlRegex = /(https?:\/\/[^\s]+)/g;
    return text.replace(urlRegex, '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 underline">$1</a>');
  };

  // Analiza el contenido
  const analyzeContent = (text: string): AnalyticsData => {
    const words = text.trim().split(/\s+/);
    const wordCount = words.length;
    const readingTime = Math.ceil(wordCount / 200); // 200 palabras por minuto
    
    // Detectar enfoque local
    const localTerms = ['malleco', 'angol', 'victoria', 'collipulli', 'araucanía', 'temuco', 'renaico'];
    const localFocus = localTerms.find(term => 
      text.toLowerCase().includes(term.toLowerCase())
    ) || 'general';
    
    // Análisis de sentimiento simple
    const positiveWords = ['bueno', 'excelente', 'éxito', 'logro', 'mejora', 'avance'];
    const negativeWords = ['malo', 'fracaso', 'problema', 'crisis', 'empeora', 'peligro'];
    const positiveCount = positiveWords.filter(word => text.toLowerCase().includes(word)).length;
    const negativeCount = negativeWords.filter(word => text.toLowerCase().includes(word)).length;
    
    let sentiment: 'positive' | 'neutral' | 'negative' = 'neutral';
    if (positiveCount > negativeCount) sentiment = 'positive';
    else if (negativeCount > positiveCount) sentiment = 'negative';
    
    // Extraer palabras clave
    const keywords = extractKeywords(text);
    
    return {
      wordCount,
      readingTime,
      localFocus,
      sentiment,
      keywords
    };
  };

  // Extrae palabras clave
  const extractKeywords = (text: string): string[] => {
    const stopWords = ['el', 'la', 'de', 'que', 'en', 'y', 'a', 'los', 'del', 'se', 'las', 'por', 'un', 'con', 'para', 'como', 'uno', 'si', 'ya', 'sus', 'al', 'lo', 'le', 'más'];
    const words = text.toLowerCase()
      .replace(/[^\w\s]/g, '')
      .split(/\s+/)
      .filter(word => word.length > 3 && !stopWords.includes(word));
    
    const wordFreq: { [key: string]: number } = {};
    words.forEach(word => {
      wordFreq[word] = (wordFreq[word] || 0) + 1;
    });
    
    return Object.entries(wordFreq)
      .sort(([,a], [,b]) => b - a)
      .slice(0, 10)
      .map(([word]) => word);
  };

  // Renderiza el contenido con anuncios y análisis
  const renderContentWithAnalytics = () => {
    const processedContent = processContent(enhancedContent || content);
    const parts = processedContent.split('<div class="native-ad-wrapper"');
    
    return (
      <div className={className}>
        {parts.map((part, index) => (
          <React.Fragment key={index}>
            <div 
              dangerouslySetInnerHTML={{ __html: part + (index < parts.length - 1 ? '"></div>' : '') }} 
              className="prose prose-lg max-w-none"
            />
            {index < parts.length - 1 && (
              <div className="my-8 fade-in">
                <NativeAd />
              </div>
            )}
          </React.Fragment>
        ))}
        
        {/* Panel de análisis */}
        {showAnalytics && analytics && (
          <div className="mt-8 p-6 bg-gray-50 rounded-xl border border-gray-200 fade-in">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">
              <i className="fas fa-chart-line text-blue-600 mr-2"></i>
              Análisis de Contenido
            </h3>
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
              <div className="text-center">
                <div className="text-2xl font-bold text-blue-600">{analytics.wordCount}</div>
                <div className="text-sm text-gray-600">Palabras</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-green-600">{analytics.readingTime}min</div>
                <div className="text-sm text-gray-600">Lectura</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-purple-600 capitalize">{analytics.localFocus}</div>
                <div className="text-sm text-gray-600">Enfoque</div>
              </div>
              <div className="text-center">
                <div className={`text-2xl font-bold capitalize ${
                  analytics.sentiment === 'positive' ? 'text-green-600' :
                  analytics.sentiment === 'negative' ? 'text-red-600' : 'text-gray-600'
                }`}>
                  {analytics.sentiment === 'positive' ? '😊' : analytics.sentiment === 'negative' ? '😟' : '😐'}
                </div>
                <div className="text-sm text-gray-600">Sentimiento</div>
              </div>
              <div className="col-span-2">
                <div className="text-sm font-medium text-gray-700 mb-2">Palabras clave:</div>
                <div className="flex flex-wrap gap-1">
                  {analytics.keywords.slice(0, 5).map((keyword, i) => (
                    <span key={i} className="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                      {keyword}
                    </span>
                  ))}
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    );
  };

  // Simula procesamiento con progreso
  const simulateProcessing = async () => {
    setProcessingState({
      isProcessing: true,
      progress: 0,
      currentStep: 'Iniciando procesamiento...'
    });

    const steps = [
      { progress: 20, step: 'Analizando contenido...' },
      { progress: 40, step: 'Detectando enfoque local...' },
      { progress: 60, step: 'Optimizando estructura...' },
      { progress: 80, step: 'Generando análisis...' },
      { progress: 100, step: 'Completando procesamiento...' }
    ];

    for (const { progress, step } of steps) {
      await new Promise(resolve => setTimeout(resolve, 500));
      setProcessingState({ isProcessing: true, progress, currentStep: step });
    }

    // Procesar contenido
    const processed = processContent(content);
    const contentAnalytics = analyzeContent(content);
    
    setEnhancedContent(processed);
    setAnalytics(contentAnalytics);
    setProcessingState({ isProcessing: false, progress: 100, currentStep: 'Completado' });
    
    if (onProcessingComplete) {
      onProcessingComplete({
        processed,
        analytics: contentAnalytics
      });
    }
  };

  // Efecto para procesar contenido cuando cambia
  useEffect(() => {
    if (content) {
      simulateProcessing();
    }
  }, [content]);

  return (
    <div className="relative">
      {/* Indicador de procesamiento */}
      {processingState.isProcessing && (
        <div className="absolute top-0 right-0 bg-white rounded-lg shadow-lg p-4 z-10 fade-in">
          <div className="flex items-center space-x-3">
            <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
            <div>
              <div className="text-sm font-medium text-gray-900">{processingState.currentStep}</div>
              <div className="w-32 bg-gray-200 rounded-full h-2 mt-1">
                <div 
                  className="bg-blue-600 h-2 rounded-full transition-all duration-300"
                  style={{ width: `${processingState.progress}%` }}
                />
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Botón de análisis */}
      <div className="mb-4 flex justify-between items-center">
        <button
          onClick={() => setShowAnalytics(!showAnalytics)}
          className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all flex items-center"
        >
          <i className="fas fa-chart-bar mr-2"></i>
          {showAnalytics ? 'Ocultar' : 'Mostrar'} Análisis
        </button>
        
        {analytics && (
          <div className="text-sm text-gray-600">
            <span className="font-medium">{analytics.wordCount}</span> palabras • 
            <span className="font-medium ml-2">{analytics.readingTime}min</span> lectura
          </div>
        )}
      </div>

      {/* Contenido procesado */}
      {renderContentWithAnalytics()}
    </div>
  );
}
