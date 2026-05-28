import { runtimeTranslator } from '$lib/utils/i18n/RuntimeTranslator.svelte';

const DAY_MS = 1000 * 60 * 60 * 24;

export function getLocale(): string {
  return runtimeTranslator.locale;
}

function toDate(input: Date | string): Date {
  if (input instanceof Date) {
    return input;
  }

  if (input.length === 10) {
    const [y, m, d] = input.split('-').map(Number);
    return new Date(y, m - 1, d);
  }

  return new Date(input);
}

export function datesEqual(a: Date | null, b: Date | null): boolean {
  return a?.getTime() === b?.getTime();
}

export function todayPlusDays(days: number): Date {
  const d = new Date();
  d.setHours(0, 0, 0, 0);
  d.setDate(d.getDate() + days);
  return d;
}

export function daysUntil(date: Date | string): number {
  const target = toDate(date);
  const now = new Date();
  const dateOnly = new Date(
    target.getFullYear(),
    target.getMonth(),
    target.getDate(),
  );
  const todayOnly = new Date(now.getFullYear(), now.getMonth(), now.getDate());

  return Math.floor((dateOnly.getTime() - todayOnly.getTime()) / DAY_MS);
}

type TimeUnit = 'day' | 'week' | 'month' | 'year';

export function narrowUnit(value: number, unit: TimeUnit): string {
  return new Intl.NumberFormat(getLocale(), {
    style: 'unit',
    unit,
    unitDisplay: 'narrow',
  }).format(value);
}

function unitFromDays(
  absDays: number,
): { value: number; unit: Exclude<TimeUnit, 'year'> } | null {
  if (absDays < 7) return { value: absDays, unit: 'day' };
  if (absDays < 30) return { value: Math.floor(absDays / 7), unit: 'week' };
  if (absDays < 365) return { value: Math.floor(absDays / 30), unit: 'month' };
  return null;
}

export function narrowFromDays(days: number): string {
  if (days < 0) return 'expired';
  if (days === 0) return 'today';
  const bucket = unitFromDays(days);
  if (bucket) return narrowUnit(bucket.value, bucket.unit);
  return narrowUnit(Math.floor(days / 365), 'year');
}

export function relativeFromDays(days: number): string {
  const locale = getLocale();
  const rtf = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });
  const absDays = Math.abs(days);

  if (absDays === 0) return rtf.format(0, 'day');

  const bucket = unitFromDays(absDays);
  if (bucket) {
    const sign = days >= 0 ? 1 : -1;
    return rtf.format(sign * bucket.value, bucket.unit);
  }

  return new Date(new Date().getTime() + days * DAY_MS).toLocaleDateString(
    locale,
  );
}

export function formatDate(date: Date | string): string {
  return relativeFromDays(daysUntil(date));
}
