import { page } from '$app/state';

export function getLocale(): string {
  return page.data.settings?.locale?.current ?? 'en';
}

function getDaysDifference(dateString: string): number {
  const date = new Date(dateString);
  const now = new Date();

  const dateOnly = new Date(
    date.getFullYear(),
    date.getMonth(),
    date.getDate(),
  );
  const todayOnly = new Date(now.getFullYear(), now.getMonth(), now.getDate());

  return Math.floor(
    (dateOnly.getTime() - todayOnly.getTime()) / (1000 * 60 * 60 * 24),
  );
}

function relativeFromDays(days: number): string {
  const locale = getLocale();
  const rtf = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });
  const absDays = Math.abs(days);
  const sign = days >= 0 ? 1 : -1;

  if (absDays === 0) return rtf.format(0, 'day');
  if (absDays < 7) return rtf.format(sign * absDays, 'day');
  if (absDays < 30) return rtf.format(sign * Math.floor(absDays / 7), 'week');
  if (absDays < 365)
    return rtf.format(sign * Math.floor(absDays / 30), 'month');

  return new Date(
    new Date().getTime() + days * 24 * 60 * 60 * 1000,
  ).toLocaleDateString(locale);
}

export function formatRelativeTime(
  days: number,
  prefix: string = '',
  suffix: string = '',
): string {
  return `${prefix}${relativeFromDays(days)}${suffix}`;
}

export function formatDate(dateString: string): string {
  return relativeFromDays(getDaysDifference(dateString));
}

export function formatExpiryDate(dateString: string): string {
  return relativeFromDays(getDaysDifference(dateString));
}

export function formatDateTime(dateString: string): string {
  return new Date(dateString).toLocaleString(getLocale());
}
