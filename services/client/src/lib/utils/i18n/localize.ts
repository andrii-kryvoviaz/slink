import { t } from './RuntimeTranslator.svelte';

export function localize(
  message: string,
  params?: Record<string, unknown>,
): string {
  const translated = t(message);

  if (!params) return translated;

  return translated.replace(/\{(\w+)\}/g, (_, key) =>
    String(params[key] ?? key),
  );
}
