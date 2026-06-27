import { ValidationException } from '@slink/api/Exceptions';

export function firstViolationMessage(
  error: unknown,
  fallback: string,
): string {
  if (!(error instanceof ValidationException)) return fallback;

  const nameViolation = error.violations.find((v) => v.property === 'name');
  return nameViolation?.message ?? error.violations[0]?.message ?? fallback;
}
