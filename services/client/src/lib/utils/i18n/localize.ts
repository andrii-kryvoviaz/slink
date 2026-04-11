export function localize(
  message: string,
  params?: Record<string, unknown>,
): string {
  if (!params) return message;
  return message.replace(/\{(\w+)\}/g, (_, key) => String(params[key] ?? key));
}
