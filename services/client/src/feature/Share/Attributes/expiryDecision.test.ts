import {
  expiryDecision,
  isShareExpired,
} from '@slink/feature/Share/Attributes/expiryDecision';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const translator = vi.hoisted(() => ({ locale: 'en-GB' }));

vi.mock('#lib/utils/i18n/RuntimeTranslator.svelte.js', () => ({
  runtimeTranslator: translator,
}));

const NOW = new Date('2026-06-15T12:00:00.000Z');

beforeEach(() => {
  vi.useFakeTimers();
  vi.setSystemTime(NOW);
});

afterEach(() => {
  vi.useRealTimers();
});

describe('isShareExpired', () => {
  it('trusts the server flag', () => {
    expect(isShareExpired('2026-06-20T12:00:00.000Z', true)).toBe(true);
  });

  it('treats a past expiry as expired even when the server flag is stale', () => {
    expect(isShareExpired('2026-06-15T11:59:59.000Z', false)).toBe(true);
  });

  it('is not expired before the expiry moment', () => {
    expect(isShareExpired('2026-06-15T12:00:01.000Z', false)).toBe(false);
  });

  it('is not expired exactly at the expiry moment', () => {
    expect(isShareExpired(NOW.toISOString(), false)).toBe(false);
  });

  it('is never expired without an expiry date', () => {
    expect(isShareExpired(null, false)).toBe(false);
  });

  it('evaluates against the given clock', () => {
    const expiresAt = '2026-06-15T12:30:00.000Z';

    expect(
      isShareExpired(expiresAt, false, new Date('2026-06-15T12:31:00.000Z')),
    ).toBe(true);
  });
});

describe('expiryDecision', () => {
  it('returns null without an expiry date', () => {
    expect(expiryDecision(null, false)).toBeNull();
  });

  it('marks a past expiry as expired despite a stale server flag', () => {
    const decision = expiryDecision('2026-06-15T11:59:59.000Z', false);

    expect(decision?.expired).toBe(true);
    expect(decision?.tone).toBe('danger');
    expect(decision?.narrow).toBe('Expired');
  });

  it('marks a server-expired share as expired', () => {
    const decision = expiryDecision('2026-06-20T12:00:00.000Z', true);

    expect(decision?.expired).toBe(true);
    expect(decision?.tone).toBe('danger');
  });

  it('keeps a near-future expiry as a warning', () => {
    const decision = expiryDecision('2026-06-15T15:00:00.000Z', false);

    expect(decision?.expired).toBe(false);
    expect(decision?.tone).toBe('warning');
    expect(decision?.narrow).toBe('3h');
  });

  it('agrees with isShareExpired at the expiry moment', () => {
    const expiresAt = NOW.toISOString();

    expect(expiryDecision(expiresAt, false)?.expired).toBe(
      isShareExpired(expiresAt, false),
    );
  });
});
