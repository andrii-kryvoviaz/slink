import { localize } from '@slink/lib/utils/i18n';

export function getUserStatusLabel(status: string): string {
  switch (status.toLowerCase()) {
    case 'active':
      return localize('Active');
    case 'inactive':
      return localize('Inactive');
    case 'suspended':
      return localize('Suspended');
    case 'banned':
      return localize('Banned');
    case 'deleted':
      return localize('Deleted');
    default:
      return status;
  }
}
