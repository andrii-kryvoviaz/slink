import { locale } from '$lib/i18n';
import en from '$lib/locales/en.json';
import zh from '$lib/locales/zh.json';
import { get } from 'svelte/store';

type ParamValue = string | number | boolean | null | undefined;
type Params = Record<string, ParamValue>;

const dictionaries = { en, zh } as const;

function interpolate(template: string, params?: Params): string {
  if (!params) return template;
  return template.replace(/\{(\w+)\}/g, (_, key: string) => {
    const value = params[key];
    return value === undefined || value === null ? '' : String(value);
  });
}

function isApiErrorKey(message: string): boolean {
  return /^[A-Z0-9_]+$/.test(message);
}

export function translateApiErrorMessage(
  message: string,
  params?: Params,
): string {
  if (!isApiErrorKey(message)) return message;

  const currentLocale = get(locale);
  const primary = (dictionaries[currentLocale] as any)?.api_errors ?? {};
  const fallback = (dictionaries.en as any)?.api_errors ?? {};
  const template = primary[message] ?? fallback[message];

  if (typeof template !== 'string') return message;
  return interpolate(template, params);
}
