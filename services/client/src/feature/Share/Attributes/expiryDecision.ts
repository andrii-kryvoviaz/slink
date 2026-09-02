import {
  daysUntil,
  formatDate,
  getLocale,
  hoursUntil,
  narrowFromDays,
  narrowUnit,
} from '@slink/lib/utils/date.svelte';

import { getExpiredLabel } from './expiry.language';

export type ExpiryTone = 'default' | 'warning' | 'danger';

export interface ExpiryDecision {
  tone: ExpiryTone;
  narrow: string;
  longDate: string;
  relative: string;
}

const toneOf = (
  isExpired: boolean,
  hours: number,
  days: number,
): ExpiryTone => {
  if (isExpired || hours < 0) return 'danger';
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

  const hours = hoursUntil(expiresAt);
  const days = daysUntil(expiresAt);
  const tone = toneOf(isExpired, hours, days);

  return {
    tone,
    narrow: narrowOf(tone, hours, days),
    longDate: new Intl.DateTimeFormat(getLocale(), {
      dateStyle: 'long',
    }).format(new Date(expiresAt)),
    relative: formatDate(expiresAt),
  };
}
