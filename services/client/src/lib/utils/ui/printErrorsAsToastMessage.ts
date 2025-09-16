import { HttpException, ValidationException } from '@slink/api/Exceptions';

import { extractErrorMessage } from '@slink/lib/utils/error/extractErrorMessage';

import { toast } from '@slink/utils/ui/toast-sonner.svelte';

import { toastComponentService } from './toastComponentRegistry.js';

function showErrorAsToast(message: string) {
  toast.error(message);
}

function showViolationsAsToasts(violations: ValidationException['violations']) {
  for (const violation of violations) {
    const config = toastComponentService.resolve(violation);

    if (config) {
      toast.component(config.component, {
        duration: config.duration || 5000,
        props: {
          message: violation.message,
          data: violation.data,
        },
      });
    } else {
      const key = violation.property === 'error' ? '' : violation.property;
      const message =
        key && violations.length > 1
          ? `[${key.capitalizeFirstLetter()}] ${violation.message}`
          : violation.message;
      showErrorAsToast(message);
    }
  }
}

function showHttpErrorsAsToasts(errors: HttpException['errors']) {
  for (const key in errors) {
    const message =
      !parseInt(key) && key && Object.keys(errors).length > 1
        ? `[${key.capitalizeFirstLetter()}] ${errors[key]}`
        : errors[key];
    showErrorAsToast(message as string);
  }
}

export function printErrorsAsToastMessage(error: Error) {
  if (error instanceof ValidationException) {
    showViolationsAsToasts(error.violations);
    return;
  }

  if (error instanceof HttpException) {
    showHttpErrorsAsToasts(error.errors);
    return;
  }

  const errorMessage = extractErrorMessage(error);
  if (errorMessage !== 'An unexpected error occurred') {
    showErrorAsToast(errorMessage);
  } else {
    showErrorAsToast('Something went wrong');
  }
}
