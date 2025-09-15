import { HttpException, ValidationException } from '@slink/api/Exceptions';

import { toast } from '@slink/utils/ui/toast-sonner.svelte';

import { toastComponentService } from './toastComponentRegistry.js';

export function printErrorsAsToastMessage(error: Error) {
  if (error instanceof ValidationException) {
    for (const violation of error.violations) {
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
          key && error.violations.length > 1
            ? `[${key.capitalizeFirstLetter()}] ${violation.message}`
            : violation.message;
        toast.error(message);
      }
    }
    return;
  }

  if (error instanceof HttpException) {
    for (const key in error.errors) {
      const message =
        !parseInt(key) && key && Object.keys(error.errors).length > 1
          ? `[${key.capitalizeFirstLetter()}] ${error.errors[key]}`
          : error.errors[key];
      toast.error(message as string);
    }
    return;
  }

  toast.error('Something went wrong');
}
