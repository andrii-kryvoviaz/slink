function pluralForm(word: string): string {
  const lower = word.toLowerCase();

  if (
    lower.endsWith('s') ||
    lower.endsWith('x') ||
    lower.endsWith('z') ||
    lower.endsWith('ch') ||
    lower.endsWith('sh')
  ) {
    return word + 'es';
  }

  if (
    lower.endsWith('y') &&
    !['a', 'e', 'i', 'o', 'u'].includes(lower[lower.length - 2])
  ) {
    return word.slice(0, -1) + 'ies';
  }

  return word + 's';
}

export function pluralize(
  count: number,
  singular: string,
  plural?: string,
): string {
  return `${count} ${count === 1 ? singular : (plural ?? pluralForm(singular))}`;
}
