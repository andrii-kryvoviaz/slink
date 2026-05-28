import { localize } from '@slink/lib/utils/i18n';

import type { ApiKeyStatus } from './ApiKeyCard.theme';

export function getApiKeyStatusLabel(status: ApiKeyStatus): string {
  switch (status) {
    case 'active':
      return localize('Active');
    case 'expired':
      return localize('Expired');
    case 'permanent':
      return localize('Permanent');
    default:
      return status;
  }
}
