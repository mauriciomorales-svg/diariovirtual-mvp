/**
 * Enlace opcional al formulario Laravel de cambio de imagen.
 * Configura NEXT_PUBLIC_ADMIN_BASE_URL (ej. https://api.diariozonasur.cl) en .env del frontend.
 * Sigue haciendo falta iniciar sesión en el admin.
 */
export default function AdminImageEditLink({
  articleId,
  className = '',
}: {
  articleId: string;
  className?: string;
}) {
  const base = process.env.NEXT_PUBLIC_ADMIN_BASE_URL?.replace(/\/$/, '');
  if (!base || !articleId) {
    return null;
  }

  const href = `${base}/admin/articles/${encodeURIComponent(articleId)}/edit`;

  return (
    <a
      href={href}
      target="_blank"
      rel="noopener noreferrer"
      title="Abre el panel para editar la noticia (requiere sesión)"
      className={
        className ||
        'inline-flex items-center gap-1 text-xs font-medium text-amber-700/90 hover:text-amber-900 bg-white/90 backdrop-blur px-2 py-1 rounded shadow-sm border border-amber-200/80'
      }
    >
      <span aria-hidden>✏️</span> Editar noticia
    </a>
  );
}
