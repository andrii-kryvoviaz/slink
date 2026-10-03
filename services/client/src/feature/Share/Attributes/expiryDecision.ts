import {
  daysUntil,
  formatDate,
  getLocale,
  hoursUntil,
  minuteClock,
  narrowFromDays,
  narrowUnit,
} from '@slink/lib/utils/date.svelte';

import { getExpiredLabel } from './expiry.language';

export type ExpiryTone = 'default' | 'warning' | 'danger';

export interface ExpiryDecision {
  expired: boolean;
  tone: ExpiryTone;
  narrow: string;
  longDate: string;
  relative: string;
}

export function isShareExpired(
  expiresAt: string | null,
  isExpired: boolean,
  now: Date = minuteClock.now,
): boolean {
  if (isExpired) return true;
  if (expiresAt === null) return false;

  return new Date(expiresAt).getTime() < now.getTime();
}

const toneOf = (expired: boolean, hours: number, days: number): ExpiryTone => {
  if (expired) return 'danger';
  if (hours < 24 || days <= 1) return 'warning';
  return 'default';
};

const narrowOf = (tone: ExpiryTone, hours: number, days: number): string => {
  if (tone === 'danger') return getExpiredLabel();
  if (hours < 24 || days < 1) return narrowUnit(hours, 'hour');
  return narrowFromDays(days);
};

export function expiryDecision(
  expiresAt: string | null,
  isExpired: boolean,
): ExpiryDecision | null {
  if (expiresAt === null) return null;

  const now = minuteClock.now;
  const expired = isShareExpired(expiresAt, isExpired, now);
  const hours = hoursUntil(expiresAt, now);
  const days = daysUntil(expiresAt, now);
  const tone = toneOf(expired, hours, days);

  return {
    expired,
    tone,
    narrow: narrowOf(tone, hours, days),
    longDate: new Intl.DateTimeFormat(getLocale(), {
      dateStyle: 'long',
    }).format(new Date(expiresAt)),
    relative: formatDate(expiresAt),
  };
}
