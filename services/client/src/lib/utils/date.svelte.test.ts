import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import {
  MinuteClock,
  calendarDayKey,
  datesEqual,
  dayKind,
  daysUntil,
  formatClockTime,
  formatDate,
  formatDateTime,
  formatDayTime,
  formatRecentTime,
  formatShortDate,
  formatShortDateTime,
  getLocale,
  hoursUntil,
  narrowFromDays,
  narrowUnit,
  relativeFromDays,
  startOfDay,
  todayPlusDays,
} from '@slink/utils/date.svelte';

const translator = vi.hoisted(() => ({ locale: 'en-GB' }));

vi.mock('$lib/utils/i18n/RuntimeTranslator.svelte', () => ({
  runtimeTranslator: translator,
}));

beforeEach(() => {
  translator.locale = 'en-GB';
});

afterEach(() => {
  vi.useRealTimers();
});

describe('startOfDay', () => {
  it('returns local midnight of the same calendar day', () => {
    const result = startOfDay(new Date(2026, 0, 5, 13, 7, 42, 999));

    expect(result.getFullYear()).toBe(2026);
    expect(result.getMonth()).toBe(0);
    expect(result.getDate()).toBe(5);
    expect(result.getHours()).toBe(0);
    expect(result.getMinutes()).toBe(0);
    expect(result.getSeconds()).toBe(0);
    expect(result.getMilliseconds()).toBe(0);
  });

  it('returns a new instance and leaves the input untouched', () => {
    const input = new Date(2026, 0, 5, 13, 7, 42, 999);
    const before = input.getTime();

    const result = startOfDay(input);

    expect(result).not.toBe(input);
    expect(input.getTime()).toBe(before);
  });

  it('returns an equal but distinct date for an input already at midnight', () => {
    const input = new Date(2026, 0, 5);

    const result = startOfDay(input);

    expect(result.getTime()).toBe(input.getTime());
    expect(result).not.toBe(input);
  });

  it('keeps the last millisecond of the year on the same day', () => {
    const result = startOfDay(new Date(2026, 11, 31, 23, 59, 59, 999));

    expect(result.getTime()).toBe(new Date(2026, 11, 31).getTime());
  });
});

describe('calendarDayKey', () => {
  it('zero-pads month and day', () => {
    expect(calendarDayKey(new Date(2026, 0, 5, 13))).toBe('2026-01-05');
  });

  it('crosses the year boundary', () => {
    expect(calendarDayKey(new Date(2026, 11, 31, 23, 59))).toBe('2026-12-31');
    expect(calendarDayKey(new Date(2027, 0, 1, 0, 0))).toBe('2027-01-01');
  });

  it('is shared by every moment of one local day and differs across midnight', () => {
    const first = calendarDayKey(new Date(2026, 8, 18, 0, 0));
    const last = calendarDayKey(new Date(2026, 8, 18, 23, 59, 59, 999));
    const next = calendarDayKey(new Date(2026, 8, 19, 0, 0));

    expect(first).toBe(last);
    expect(next).not.toBe(last);
  });

  it('uses the local day rather than UTC', () => {
    expect(calendarDayKey(new Date(2026, 8, 18, 0, 30))).toBe('2026-09-18');
  });

  it('formats a leap day', () => {
    const key = calendarDayKey(new Date(2028, 1, 29));

    expect(key).toMatch(/^\d{4}-\d{2}-\d{2}$/);
    expect(key).toBe('2028-02-29');
  });
});

describe('dayKind', () => {
  it('is today for two moments on the same calendar day', () => {
    expect(
      dayKind(new Date(2026, 8, 18, 0, 0), new Date(2026, 8, 18, 23, 59)),
    ).toBe('today');
  });

  it('is yesterday across midnight even two minutes apart', () => {
    expect(
      dayKind(new Date(2026, 8, 17, 23, 59), new Date(2026, 8, 18, 0, 1)),
    ).toBe('yesterday');
  });

  it('is earlier two calendar days back', () => {
    expect(dayKind(new Date(2026, 8, 16, 12), new Date(2026, 8, 18))).toBe(
      'earlier',
    );
  });

  it('is yesterday for moments almost 48 hours apart on adjacent days', () => {
    expect(
      dayKind(new Date(2026, 8, 17, 0, 0), new Date(2026, 8, 18, 23, 59)),
    ).toBe('yesterday');
  });

  it('is yesterday across month and year rollover', () => {
    expect(dayKind(new Date(2026, 11, 31, 22), new Date(2027, 0, 1, 1))).toBe(
      'yesterday',
    );
    expect(dayKind(new Date(2026, 1, 28, 12), new Date(2026, 2, 1, 9))).toBe(
      'yesterday',
    );
  });

  it('is earlier for the same month and day a year back', () => {
    expect(dayKind(new Date(2025, 8, 18), new Date(2026, 8, 18))).toBe(
      'earlier',
    );
  });

  it('defaults now to the system clock', () => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date(2026, 8, 18, 10));

    expect(dayKind(new Date(2026, 8, 18, 1))).toBe('today');
    expect(dayKind(new Date(2026, 8, 17, 1))).toBe('yesterday');
  });

  it('is yesterday across daylight saving changes', () => {
    expect(dayKind(new Date(2026, 2, 28, 12), new Date(2026, 2, 29, 12))).toBe(
      'yesterday',
    );
    expect(dayKind(new Date(2026, 9, 24, 12), new Date(2026, 9, 25, 12))).toBe(
      'yesterday',
    );
    expect(dayKind(new Date(2026, 2, 7, 12), new Date(2026, 2, 8, 12))).toBe(
      'yesterday',
    );
    expect(dayKind(new Date(2026, 10, 1, 12), new Date(2026, 10, 2, 12))).toBe(
      'yesterday',
    );
  });

  it('is earlier for a future day', () => {
    expect(dayKind(new Date(2026, 8, 20), new Date(2026, 8, 18))).toBe(
      'earlier',
    );
  });
});

describe('formatClockTime', () => {
  it('uses the 24-hour clock for en-GB', () => {
    expect(formatClockTime(new Date(2026, 0, 5, 14, 5))).toBe('14:05');
  });

  it('uses the 12-hour clock with a period for en-US', () => {
    translator.locale = 'en-US';

    const result = formatClockTime(new Date(2026, 0, 5, 14, 5));

    expect(result).toContain('2:05');
    expect(result).toMatch(/PM/i);
  });

  it('pads the midnight hour to two digits', () => {
    expect(formatClockTime(new Date(2026, 0, 5, 0, 7))).toBe('00:07');
  });

  it('pads the hour for de', () => {
    translator.locale = 'de';

    expect(formatClockTime(new Date(2026, 0, 5, 9, 3))).toBe('09:03');
  });

  it('reads the locale on every call', () => {
    const date = new Date(2026, 0, 5, 14, 5);
    const british = formatClockTime(date);

    translator.locale = 'en-US';

    expect(formatClockTime(date)).not.toBe(british);
  });

  it('omits seconds and date parts', () => {
    expect(formatClockTime(new Date(2026, 0, 5, 14, 5, 30))).not.toMatch(
      /\d{1,2}:\d{2}:\d{2}/,
    );
  });
});

describe('formatShortDate', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date(2026, 8, 18, 10));
  });

  it('puts the day first for en-GB in the current year', () => {
    expect(formatShortDate(new Date(2026, 6, 14))).toBe('14 Jul');
  });

  it('puts the month first for en-US in the current year', () => {
    translator.locale = 'en-US';

    expect(formatShortDate(new Date(2026, 6, 14))).toBe('Jul 14');
  });

  it('appends the year only when it differs from the current year', () => {
    expect(formatShortDate(new Date(2025, 6, 14))).toBe('14 Jul 2025');
    expect(formatShortDate(new Date(2026, 6, 14))).not.toContain('2026');
  });

  it('reads the locale on every call', () => {
    const date = new Date(2026, 6, 14);
    const british = formatShortDate(date);

    translator.locale = 'en-US';

    expect(formatShortDate(date)).not.toBe(british);
  });
});

describe('formatShortDateTime', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date(2026, 8, 18, 10));
  });

  it('joins the short date and the clock time with a comma', () => {
    expect(formatShortDateTime(new Date(2026, 6, 11, 18, 2))).toBe(
      '11 Jul, 18:02',
    );
  });
});

describe('formatDayTime', () => {
  const now = new Date(2026, 8, 18, 10);

  beforeEach(() => {
    vi.useFakeTimers();
    vi.setSystemTime(now);
  });

  it('shows the clock time for today', () => {
    expect(formatDayTime(new Date(2026, 8, 18, 9, 5), now)).toBe('09:05');
  });

  it('shows the clock time for yesterday', () => {
    expect(formatDayTime(new Date(2026, 8, 17, 23, 40), now)).toBe('23:40');
  });

  it('shows a short date for earlier days', () => {
    expect(formatDayTime(new Date(2026, 7, 14, 9, 5), now)).toBe('14 Aug');
  });

  it('appends the year for a previous year', () => {
    expect(formatDayTime(new Date(2025, 7, 14, 9, 5), now)).toBe('14 Aug 2025');
  });

  it('follows the app locale', () => {
    translator.locale = 'en-US';

    expect(formatDayTime(new Date(2026, 7, 14), now)).toBe('Aug 14');
  });
});

describe('formatRecentTime', () => {
  const now = new Date(2026, 8, 18, 10);
  const ago = (ms: number) => new Date(now.getTime() - ms);
  const SECOND = 1000;
  const MINUTE = SECOND * 60;
  const HOUR = MINUTE * 60;

  beforeEach(() => {
    vi.useFakeTimers();
    vi.setSystemTime(now);
  });

  it('shows now for the last minute', () => {
    expect(formatRecentTime(ago(0), now)).toBe('now');
    expect(formatRecentTime(ago(20 * SECOND), now)).toBe('now');
    expect(formatRecentTime(ago(59 * SECOND), now)).toBe('now');
  });

  it('shows whole minutes under an hour', () => {
    expect(formatRecentTime(ago(MINUTE), now)).toBe('1m');
    expect(formatRecentTime(ago(35 * MINUTE), now)).toBe('35m');
    expect(formatRecentTime(ago(59 * MINUTE + 59 * SECOND), now)).toBe('59m');
  });

  it('shows whole hours for the rest of today', () => {
    expect(formatRecentTime(ago(HOUR), now)).toBe('1h');
    expect(formatRecentTime(ago(2 * HOUR + 10 * MINUTE), now)).toBe('2h');
    expect(formatRecentTime(ago(2 * HOUR + 59 * MINUTE), now)).toBe('2h');
    expect(formatRecentTime(new Date(2026, 8, 18, 0, 0, 30), now)).toBe('9h');
  });

  it('keeps the day time for yesterday', () => {
    const date = new Date(2026, 8, 17, 23, 40);

    expect(formatRecentTime(date, now)).toBe(formatDayTime(date, now));
    expect(formatRecentTime(date, now)).toBe('23:40');
  });

  it('lets the yesterday rule win across midnight', () => {
    const midnight = new Date(2026, 8, 18, 0, 10);
    const date = new Date(2026, 8, 17, 23, 50);

    expect(formatRecentTime(date, midnight)).toBe(
      formatDayTime(date, midnight),
    );
  });

  it('keeps the short date for earlier days', () => {
    const date = new Date(2026, 8, 11, 9, 5);

    expect(formatRecentTime(date, now)).toBe(formatDayTime(date, now));
    expect(formatRecentTime(date, now)).not.toMatch(/^(now|\d+[mh])$/);
  });

  it('clamps future timestamps to now', () => {
    expect(formatRecentTime(ago(-30 * SECOND), now)).toBe('now');
    expect(formatRecentTime(ago(-2 * HOUR), now)).toBe('now');
  });

  it('defaults now to the current time', () => {
    const date = ago(35 * MINUTE);

    expect(formatRecentTime(date)).toBe(formatRecentTime(date, now));
  });

  it('localises every supported locale', () => {
    for (const locale of [
      'en',
      'de',
      'es',
      'fr',
      'it',
      'pl',
      'uk',
      'ja',
      'zh',
    ]) {
      translator.locale = locale;

      const recent = formatRecentTime(ago(20 * SECOND), now);
      const minutes = formatRecentTime(ago(5 * MINUTE), now);
      const later = formatRecentTime(ago(50 * MINUTE), now);

      expect(recent).not.toBe('');
      expect(minutes).not.toBe('');
      expect(minutes).not.toBe(later);
      expect(recent).not.toBe(minutes);
    }
  });

  it('keeps the digits in a localised minute count', () => {
    translator.locale = 'uk';

    expect(formatRecentTime(ago(20 * SECOND), now)).not.toBe('now');
    expect(formatRecentTime(ago(35 * MINUTE), now)).toContain('35');
  });
});

describe('narrowUnit', () => {
  it('formats minutes narrowly', () => {
    translator.locale = 'en';

    expect(narrowUnit(35, 'minute')).toBe('35m');
  });
});

describe('MinuteClock', () => {
  const start = new Date(2026, 8, 18, 12);
  const SECOND = 1000;
  const MINUTE = SECOND * 60;

  beforeEach(() => {
    vi.useFakeTimers();
    vi.setSystemTime(start);
  });

  it('reads a fresh time when nothing is subscribed', () => {
    const clock = new MinuteClock();

    expect(clock.now.getTime()).toBe(start.getTime());

    vi.advanceTimersByTime(5 * MINUTE);

    expect(clock.now.getTime()).toBe(start.getTime() + 5 * MINUTE);
    expect(vi.getTimerCount()).toBe(0);
  });

  it('hands out a distinct time on every untracked read', () => {
    const clock = new MinuteClock();

    const first = clock.now;
    vi.advanceTimersByTime(90 * SECOND);
    const second = clock.now;
    vi.advanceTimersByTime(90 * SECOND);
    const third = clock.now;

    expect(second.getTime()).toBeGreaterThan(first.getTime());
    expect(third.getTime()).toBeGreaterThan(second.getTime());
    expect(second).not.toBe(first);
    expect(third).not.toBe(second);
    expect(vi.getTimerCount()).toBe(0);
  });
});

describe('formatDateTime', () => {
  it('includes the year and the time', () => {
    const result = formatDateTime(new Date(2026, 7, 14, 9, 5));

    expect(result).toContain('2026');
    expect(result).toContain('09:05');
  });

  it('follows the app locale', () => {
    const date = new Date(2026, 7, 14, 9, 5);
    const british = formatDateTime(date);

    translator.locale = 'en-US';

    expect(formatDateTime(date)).not.toBe(british);
  });
});

describe('date helper contract', () => {
  it('never mutates the date argument', () => {
    const helpers = [
      startOfDay,
      calendarDayKey,
      (date: Date) => dayKind(date, new Date(2026, 8, 18)),
      formatClockTime,
      formatShortDate,
      (date: Date) => formatDayTime(date, new Date(2026, 8, 18)),
      formatDateTime,
    ];

    for (const helper of helpers) {
      const input = new Date(2026, 8, 17, 13, 7, 42, 999);
      const before = input.getTime();

      helper(input);

      expect(input.getTime()).toBe(before);
    }
  });

  it('keys a seconds timestamp converted by the caller', () => {
    const date = new Date(1789000000 * 1000);
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    expect(calendarDayKey(date)).toBe(`${date.getFullYear()}-${month}-${day}`);
  });

  it('keeps the existing exports', () => {
    const exports = [
      getLocale,
      datesEqual,
      todayPlusDays,
      daysUntil,
      hoursUntil,
      narrowUnit,
      narrowFromDays,
      relativeFromDays,
      formatDate,
    ];

    for (const fn of exports) {
      expect(fn).toBeTypeOf('function');
    }
  });

  it('still formats dates as relative wording', () => {
    translator.locale = 'en';

    const result = formatDate(new Date());

    expect(result).toMatch(/now|today|second|minute/i);
    expect(result).not.toMatch(/\d{1,2}:\d{2}/);
  });
});
