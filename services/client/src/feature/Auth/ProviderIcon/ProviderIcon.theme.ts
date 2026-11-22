import { cva } from 'class-variance-authority';

export const providerIconVariants = cva('', {
  variants: {
    provider: {
      google: '',
      authentik: 'text-brand-authentik',
      keycloak: 'text-foreground-muted',
      authelia: 'text-brand-authelia',
      pocketid: 'text-brand-pocketid',
    },
  },
});
