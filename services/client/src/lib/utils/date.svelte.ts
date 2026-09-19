import { runtimeTranslator } from '$lib/utils/i18n/RuntimeTranslator.svelte';

const HOUR_MS = 1000 * 60 * 60;
const DAY_MS = HOUR_MS * 24;

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

export function hoursUntil(date: Date | string): number {
  return Math.floor((toDate(date).getTime() - Date.now()) / HOUR_MS);
}

type TimeUnit = 'hour' | 'day' | 'week' | 'month' | 'year';

export function narrowUnit(value: number, unit: TimeUnit): string {
  return new Intl.NumberFormat(getLocale(), {
    style: 'unit',
    unit,
    unitDisplay: 'narrow',
  }).format(value);
}

function unitFromDays(
  absDays: number,
): { value: number; unit: Exclude<TimeUnit, 'hour' | 'year'> } | null {
  if (absDays < 7) return { value: absDays, unit: 'day' };
  if (absDays < 30) return { value: Math.floor(absDays / 7), unit: 'week' };
  if (absDays < 365) return { value: Math.floor(absDays / 30), unit: 'month' };
  return null;
}

export function narrowFromDays(days: number): string {
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

export type DayKind = 'today' | 'yesterday' | 'earlier';

export function startOfDay(date: Date): Date {
  return new Date(date.getFullYear(), date.getMonth(), date.getDate());
}

export function calendarDayKey(date: Date): string {
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${date.getFullYear()}-${month}-${day}`;
}

export function dayKind(date: Date, now: Date = new Date()): DayKind {
  const diff = Math.round(
    (startOfDay(now).getTime() - startOfDay(date).getTime()) / DAY_MS,
  );

  if (diff === 0) return 'today';
  if (diff === 1) return 'yesterday';
  return 'earlier';
}

export function formatClockTime(date: Date): string {
  return new Intl.DateTimeFormat(getLocale(), {
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
}

export function formatShortDate(date: Date): string {
  const options: Intl.DateTimeFormatOptions = {
    month: 'short',
    day: 'numeric',
  };

  if (date.getFullYear() !== new Date().getFullYear()) {
    options.year = 'numeric';
  }

  return new Intl.DateTimeFormat(getLocale(), options).format(date);
}

export function formatDayTime(date: Date, now: Date = new Date()): string {
  if (dayKind(date, now) === 'earlier') {
    return formatShortDate(date);
  }

  return formatClockTime(date);
}

export function formatShortDateTime(date: Date): string {
  return `${formatShortDate(date)}, ${formatClockTime(date)}`;
}

export function formatDateTime(date: Date): string {
  return new Intl.DateTimeFormat(getLocale(), {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(date);
}
