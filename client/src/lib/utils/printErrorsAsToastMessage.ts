import { HttpException } from '@slink/api/Exceptions';
import { toast } from '@slink/store/toast';

export function printErrorsAsToastMessage(error: Error) {
  if (error instanceof HttpException) {
    error.errors.forEach((error) => {
      toast.error(error.message);
    });

    return;
  }

  toast.error('Something went wrong');
}
