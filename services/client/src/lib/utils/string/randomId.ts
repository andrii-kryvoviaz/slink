export function randomId(prefix = 'id', length = 10): string {
  return `${prefix}-${Math.random().toString(36).substring(0, length)}`;
}
