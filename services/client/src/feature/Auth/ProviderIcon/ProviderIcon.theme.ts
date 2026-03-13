import { cva } from 'class-variance-authority';

export const providerIconVariants = cva('', {
  variants: {
    provider: {
      google: '',
      authentik: 'text-orange-600',
      keycloak: 'text-gray-600 dark:text-gray-300',
      authelia: 'text-blue-600',
      pocketid: 'text-rose-500',
    },
  },
});
