// Native Ad injection utility
export function injectAds(content: string): string {
  if (!content) return content;
  
  // Split content by paragraphs
  const parts = content.split('</p>');
  
  // Inject placeholder after second paragraph (index 1)
  if (parts.length > 2) {
    parts.splice(2, 0, '<div data-native-ad="true"></div>');
  }
  
  return parts.join('</p>');
}
