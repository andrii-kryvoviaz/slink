import { ValidationException } from '@slink/api/Exceptions/ValidationException';
import { toast } from '@slink/store/toast';

export function printErrorsAsToastMessage(error: Error) {
  if (error instanceof ValidationException) {
    error.violations.forEach((violation) => {
      toast.error(violation.message);
    });
  } else {
    toast.error('Something went wrong');
  }
}
