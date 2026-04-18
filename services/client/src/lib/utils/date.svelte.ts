import { page } from '$app/state';

const DAY_MS = 1000 * 60 * 60 * 24;

export function getLocale(): string {
  return page.data.settings?.locale?.current ?? 'en';
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

export function relativeFromDays(days: number): string {
  const locale = getLocale();
  const rtf = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });
  const absDays = Math.abs(days);
  const sign = days >= 0 ? 1 : -1;

  if (absDays === 0) return rtf.format(0, 'day');
  if (absDays < 7) return rtf.format(sign * absDays, 'day');
  if (absDays < 30) return rtf.format(sign * Math.floor(absDays / 7), 'week');
  if (absDays < 365)
    return rtf.format(sign * Math.floor(absDays / 30), 'month');

  return new Date(new Date().getTime() + days * DAY_MS).toLocaleDateString(
    locale,
  );
}

export function formatDate(date: Date | string): string {
  return relativeFromDays(daysUntil(date));
}
