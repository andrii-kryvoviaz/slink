export function formatMimeType(mimeType: string): string {
  const type = mimeType.split('/')[1];
  if (!type) return mimeType;
  return type.toUpperCase();
}
