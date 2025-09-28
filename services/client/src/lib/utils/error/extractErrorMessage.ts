import { HttpException, ValidationException } from '@slink/api/Exceptions';

export function extractErrorMessage(
  error: Error,
  fallbackMessage: string = 'An unexpected error occurred',
): string {
  if (error instanceof ValidationException) {
    if (error.violations.length > 1) {
      return error.violations
        .map((violation) => {
          const key = violation.property === 'error' ? '' : violation.property;
          return key ? `[${key}] ${violation.message}` : violation.message;
        })
        .join('\n');
    }
    const violation = error.violations[0];
    return violation?.message || 'Validation failed';
  }

  if (error instanceof HttpException) {
    const errors = Object.values(error.errors);
    if (errors.length > 1) {
      return Object.entries(error.errors)
        .map(([key, message]) => {
          const displayKey = !parseInt(key) && key ? `[${key}]` : '';
          return displayKey ? `${displayKey} ${message}` : String(message);
        })
        .join('\n');
    }
    const firstError = errors[0];
    return typeof firstError === 'string' ? firstError : String(firstError);
  }

  return error.message || fallbackMessage;
}

export function extractShortErrorMessage(error: Error): string {
  if (error instanceof ValidationException) {
    const firstViolation = error.violations[0];
    return firstViolation?.message || 'Validation failed';
  }

  if (error instanceof HttpException) {
    const firstError = Object.values(error.errors)[0];
    return typeof firstError === 'string' ? firstError : String(firstError);
  }

  return error.message || 'An unexpected error occurred';
}
