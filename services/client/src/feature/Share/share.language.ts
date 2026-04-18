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
};
