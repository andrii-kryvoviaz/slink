import {
  DuplicateImageToast,
  UnsupportedFormatToast,
} from '@slink/ui/components/sonner/toasts/index.js';

import { toastComponentService } from './ToastComponentService.js';

toastComponentService.registerWithData(
  'duplicate_image',
  DuplicateImageToast,
  8000,
);

toastComponentService.register('unsupported_format', {
  component: UnsupportedFormatToast,
  duration: 6000,
});

export { toastComponentService };
