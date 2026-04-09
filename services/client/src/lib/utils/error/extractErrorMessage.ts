import { HttpException, ValidationException } from '@slink/api/Exceptions';

import { translateApiErrorMessage } from '@slink/lib/utils/error/translateApiErrorMessage';

export function extractErrorMessage(
  error: Error,
  fallbackMessage: string = 'An unexpected error occurred',
): string {
  if (error instanceof ValidationException) {
    if (error.violations.length > 1) {
      return error.violations
        .map((violation) => {
          const key = violation.property === 'error' ? '' : violation.property;
          const translated = translateApiErrorMessage(
            violation.message,
            violation.data?.params as
              | Record<string, string | number>
              | undefined,
          );
          return key ? `[${key}] ${translated}` : translated;
        })
        .join('\n');
    }
    const violation = error.violations[0];
    return violation
      ? translateApiErrorMessage(
          violation.message,
          violation.data?.params as Record<string, string | number> | undefined,
        )
      : 'Validation failed';
  }

  if (error instanceof HttpException) {
    const params = (
      error as unknown as { params?: Record<string, string | number> }
    ).params;
    const errors = Object.values(error.errors);
    if (errors.length > 1) {
      return Object.entries(error.errors)
        .map(([key, message]) => {
          const displayKey = !parseInt(key) && key ? `[${key}]` : '';
          const translated =
            typeof message === 'string'
              ? translateApiErrorMessage(message, params)
              : String(message);
          return displayKey ? `${displayKey} ${translated}` : translated;
        })
        .join('\n');
    }
    const firstError = errors[0];
    return typeof firstError === 'string'
      ? translateApiErrorMessage(firstError, params)
      : String(firstError);
  }

  return error.message || fallbackMessage;
}

export function extractShortErrorMessage(error: Error): string {
  if (error instanceof ValidationException) {
    const firstViolation = error.violations[0];
    return firstViolation
      ? translateApiErrorMessage(
          firstViolation.message,
          firstViolation.data?.params as
            | Record<string, string | number>
            | undefined,
        )
      : 'Validation failed';
  }

  if (error instanceof HttpException) {
    const params = (
      error as unknown as { params?: Record<string, string | number> }
    ).params;
    const firstError = Object.values(error.errors)[0];
    return typeof firstError === 'string'
      ? translateApiErrorMessage(firstError, params)
      : String(firstError);
  }

  return error.message || 'An unexpected error occurred';
}
