import { localize } from '$lib/utils/i18n/localize';

export const shareMessages = {
  status: {
    saving: () => localize('Saving expiration'),
    saved: () => localize('Saved'),
    error: () => localize('Failed to save expiration'),
  },
  expirationDescription: {
    expired: () => localize('Expired'),
    today: () => localize('Expires today'),
    inFuture: (phrase: string) => localize('Expires {phrase}', { phrase }),
  },
  expirationShort: {
    expired: () => localize('Expired'),
    today: () => localize('Today'),
  },
  password: {
    status: {
      saving: () => localize('Saving password'),
      saved: () => localize('Saved'),
      error: () => localize('Failed to save password'),
    },
    minLengthHint: (min: number) =>
      localize('Use at least {min} characters', { min: String(min) }),
  },
  locked: {
    invalid: () => localize('Incorrect password. Try again.'),
    error: () => localize('Something went wrong. Please try again.'),
  },
};
