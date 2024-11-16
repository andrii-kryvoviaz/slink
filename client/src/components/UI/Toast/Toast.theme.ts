import type { ToastIcon } from '@slink/components/UI/Toast/Toast.types';

export const commonToastThemeMap: Map<string, ToastIcon> = new Map([
  [
    'success',
    {
      icon: 'clarity:success-standard-line',
      iconColor: 'text-green-500',
    },
  ],
  [
    'warning',
    {
      icon: 'clarity:warning-standard-line',
      iconColor: 'text-yellow-400',
    },
  ],
  [
    'error',
    {
      icon: 'clarity:error-standard-line',
      iconColor: 'text-red-500',
    },
  ],
  [
    'info',
    {
      icon: 'clarity:info-standard-line',
      iconColor: 'text-blue-500',
    },
  ],
]);
