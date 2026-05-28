import { localize } from '@slink/lib/utils/i18n';

export { getUserStatusLabel } from '@slink/feature/User/UserStatus/userStatus.language';

export function getTotalLabel(): string {
  return localize('Total');
}

export function getIntervalLabel(value: string): string {
  switch (value) {
    case 'today':
      return localize('Today');
    case 'current_week':
      return localize('Current Week');
    case 'last_week':
      return localize('Last Week');
    case 'last_7_days':
      return localize('Last 7 Days');
    case 'current_month':
      return localize('Current Month');
    case 'last_month':
      return localize('Last Month');
    case 'last_30_days':
      return localize('Last 30 Days');
    case 'current_year':
      return localize('Current Year');
    case 'all_time':
      return localize('All Time');
    default:
      return value;
  }
}
