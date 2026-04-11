type PluralRule = (n: number) => number;

const defaultRule: PluralRule = (n) => (n === 1 ? 0 : 1);

export function plural(
  count: number,
  candidates: string[],
  rule: PluralRule = defaultRule,
): string {
  const index = Math.min(rule(count), candidates.length - 1);
  return candidates[index].replace(/#/g, String(count));
}
