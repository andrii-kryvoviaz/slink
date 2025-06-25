import { HttpException } from '@slink/api/Exceptions';
import { toast } from '@slink/utils/ui/toast.svelte';

export function printErrorsAsToastMessage(error: Error) {
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
