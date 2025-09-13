function getDaysDifference(dateString: string): number {
  const date = new Date(dateString);
  const now = new Date();

  const dateOnly = new Date(
    date.getFullYear(),
    date.getMonth(),
    date.getDate(),
  );
  const todayOnly = new Date(now.getFullYear(), now.getMonth(), now.getDate());

  return Math.floor(
    (dateOnly.getTime() - todayOnly.getTime()) / (1000 * 60 * 60 * 24),
  );
}

function formatTimeUnit(value: number, unit: string): string {
  return `${value} ${unit}${value > 1 ? 's' : ''}`;
}

export function formatRelativeTime(
  days: number,
  prefix: string = '',
  suffix: string = '',
): string {
  const absDays = Math.abs(days);

  if (absDays === 0) return `${prefix}today`;
  if (absDays === 1) return `${prefix}${days > 0 ? 'tomorrow' : 'yesterday'}`;
  if (absDays < 7)
    return `${prefix}${suffix ? 'in ' : ''}${formatTimeUnit(absDays, 'day')}${suffix}`;
  if (absDays < 30)
    return `${prefix}${suffix ? 'in ' : ''}${formatTimeUnit(Math.floor(absDays / 7), 'week')}${suffix}`;
  if (absDays < 365)
    return `${prefix}${suffix ? 'in ' : ''}${formatTimeUnit(Math.floor(absDays / 30), 'month')}${suffix}`;

  return `${prefix}${suffix ? 'on ' : ''}${new Date(new Date().getTime() + days * 24 * 60 * 60 * 1000).toLocaleDateString()}`;
}

export function formatDate(dateString: string): string {
  const days = -getDaysDifference(dateString);

  if (days === 0) return 'Today';
  if (days === 1) return 'Yesterday';
  if (days < 7) return `${days} days ago`;
  if (days < 30)
    return `${Math.floor(days / 7)} week${Math.floor(days / 7) > 1 ? 's' : ''} ago`;
  if (days < 365)
    return `${Math.floor(days / 30)} month${Math.floor(days / 30) > 1 ? 's' : ''} ago`;

  return new Date(dateString).toLocaleDateString();
}

export function formatExpiryDate(dateString: string): string {
  const days = getDaysDifference(dateString);

  if (days < 0) {
    const absDays = Math.abs(days);
    if (absDays === 0) return 'Expired today';
    if (absDays === 1) return 'Expired yesterday';
    if (absDays < 7) return `Expired ${absDays} days ago`;
    if (absDays < 30)
      return `Expired ${Math.floor(absDays / 7)} week${Math.floor(absDays / 7) > 1 ? 's' : ''} ago`;
    if (absDays < 365)
      return `Expired ${Math.floor(absDays / 30)} month${Math.floor(absDays / 30) > 1 ? 's' : ''} ago`;
    return `Expired on ${new Date(dateString).toLocaleDateString()}`;
  }

  if (days === 0) return 'Expires today';
  if (days === 1) return 'Expires tomorrow';
  if (days < 7) return `Expires in ${days} days`;
  if (days < 30)
    return `Expires in ${Math.floor(days / 7)} week${Math.floor(days / 7) > 1 ? 's' : ''}`;
  if (days < 365)
    return `Expires in ${Math.floor(days / 30)} month${Math.floor(days / 30) > 1 ? 's' : ''}`;

  return `Expires on ${new Date(dateString).toLocaleDateString()}`;
}

export function formatDateTime(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleString();
}
