import { cva } from 'class-variance-authority';

export const providerIconVariants = cva('', {
  variants: {
    provider: {
      google: '',
      authentik: 'text-[#fd4b2d]',
      keycloak: 'text-muted-foreground-strong',
      authelia: 'text-[#0065BF]',
      pocketid: 'text-[#e11d48]',
    },
  },
});
