import accentSource from '../../../theme/core/accent.css?raw';
import decorSource from '../../../theme/core/decor.css?raw';
import radiusSource from '../../../theme/core/radius.css?raw';
import seriesSource from '../../../theme/core/series.css?raw';
import statusSource from '../../../theme/core/status.css?raw';
import surfaceSource from '../../../theme/core/surface.css?raw';
import defaultSource from '../../../theme/presets/default.css?raw';

const tokensSource = [
  defaultSource,
  radiusSource,
  surfaceSource,
  accentSource,
  decorSource,
  seriesSource,
  statusSource,
].join('\n');

type CssBlock = { selector: string; body: string };

const DARK_SELECTOR = '.dark';

const readBlocks = (source: string): CssBlock[] => {
  const blocks: CssBlock[] = [];
  let depth = 0;
  let bodyStart = 0;
  let selectorStart = 0;

  for (let index = 0; index < source.length; index++) {
    const char = source[index];

    if (char === '{') {
      depth += 1;
      if (depth === 1) bodyStart = index + 1;
      continue;
    }

    if (char !== '}') continue;

    depth -= 1;
    if (depth > 0) continue;

    blocks.push({
      selector: source.slice(selectorStart, bodyStart - 1).trim(),
      body: source.slice(bodyStart, index),
    });
    selectorStart = index + 1;
  }

  return blocks;
};

const blocks = readBlocks(tokensSource);

const bodyOf = (selectors: (selector: string) => boolean) =>
  blocks
    .filter((block) => selectors(block.selector))
    .map((block) => block.body)
    .join('\n');

const baseBody = bodyOf((selector) => selector !== DARK_SELECTOR);
const darkBody = bodyOf((selector) => selector === DARK_SELECTOR);

export const SCOPE_ATTRIBUTE = 'data-token-scope';

export const scopedTokenCss = [
  `[${SCOPE_ATTRIBUTE}][${SCOPE_ATTRIBUTE}]{${baseBody}}`,
  `[${SCOPE_ATTRIBUTE}='dark'][${SCOPE_ATTRIBUTE}]{${darkBody}}`,
].join('\n');

const declaredNames = (body: string) =>
  [...body.matchAll(/(?:^|;)\s*(--[\w-]+)\s*:/g)].map(
    (match) => match[1] ?? '',
  );

export type TokenGroup = { name: string; tokens: string[] };

const groupOf = (token: string) => token.slice(2).split('-')[0] ?? 'other';

const collectGroups = (names: string[]): TokenGroup[] => {
  const groups = new Map<string, string[]>();

  for (const name of names) {
    const group = groups.get(groupOf(name)) ?? [];
    if (!group.includes(name)) group.push(name);
    groups.set(groupOf(name), group);
  }

  return [...groups].map(([name, tokens]) => ({ name, tokens }));
};

export const tokenNames = [
  ...new Set([...declaredNames(baseBody), ...declaredNames(darkBody)]),
];

export const tokenGroups = collectGroups(tokenNames);

const matchBrace = (source: string, open: number) => {
  let depth = 0;

  for (let index = open; index < source.length; index++) {
    const char = source[index];
    if (char === '{') depth += 1;
    if (char !== '}') continue;
    depth -= 1;
    if (depth === 0) return index;
  }

  return source.length;
};

const objectKeys = (body: string): string[] => {
  const keys: string[] = [];
  const openers = '{[(';
  const closers = '}])';
  let depth = 0;
  let quote = '';
  let token = '';

  for (let index = 0; index < body.length; index++) {
    const char = body[index] ?? '';

    if (quote) {
      if (char === '\\') index += 1;
      else if (char === quote) quote = '';
      else token += char;
      continue;
    }

    if (char === '"' || char === "'" || char === '`') {
      quote = char;
      token = '';
      continue;
    }

    if (char === ':') {
      if (depth === 0 && token.trim()) keys.push(token.trim());
      token = '';
      continue;
    }

    if (openers.includes(char)) {
      depth += 1;
      token = '';
      continue;
    }

    if (closers.includes(char)) {
      depth -= 1;
      token = '';
      continue;
    }

    if (char === ',') {
      token = '';
      continue;
    }

    token += char;
  }

  return keys;
};

const escapeKey = (key: string) => key.replace(/[^\w]/g, (char) => `\\${char}`);

const blockFor = (source: string, key: string) => {
  const opener = new RegExp(`['"\`]?${escapeKey(key)}['"\`]?\\s*:\\s*\\{`).exec(
    source,
  );
  if (!opener) return '';

  const open = opener.index + opener[0].length - 1;
  return source.slice(open + 1, matchBrace(source, open));
};

const variantsBlockOf = (definition: string) => {
  const opener = /(?<![A-Za-z])variants\s*:\s*\{/.exec(definition);
  if (!opener) return '';

  const open = opener.index + opener[0].length - 1;
  return definition.slice(open + 1, matchBrace(definition, open));
};

export type VariantEntry = { key: string; values: string[] };

const parseDefinitions = (source: string) => {
  const definitions = new Map<string, VariantEntry[]>();
  const declaration = /(?:export\s+)?const\s+(\w+)\s*=\s*(?:cva|tv)\(/g;

  for (const match of source.matchAll(declaration)) {
    const boundary = /\n\s*(?:export\s+)?(?:const|type|function)\s/g;
    boundary.lastIndex = match.index + match[0].length;
    const next = boundary.exec(source);
    const variants = variantsBlockOf(
      source.slice(match.index, next ? next.index : source.length),
    );

    if (!variants) continue;

    definitions.set(
      match[1] ?? '',
      objectKeys(variants).map((key) => ({
        key,
        values: objectKeys(blockFor(variants, key)),
      })),
    );
  }

  return definitions;
};

export const variantValues = (
  source: string,
  definition: string,
  key: string,
): string[] =>
  parseDefinitions(source)
    .get(definition)
    ?.find((entry) => entry.key === key)?.values ?? [];

type ThemeFn = (props?: Record<string, unknown>) => unknown;

export type ThemeDefinition = {
  name: string;
  entries: VariantEntry[];
  render: (key: string, value: string) => string[];
};

export type ThemeFile = { label: string; definitions: ThemeDefinition[] };

const rawThemes = import.meta.glob(
  [
    '/src/ui/components/**/*.theme.ts',
    '/src/ui/components/**/*.variants.ts',
    '/src/ui/components/popover/themes.ts',
  ],
  { query: '?raw', import: 'default', eager: true },
) as Record<string, string>;

const themeModules = import.meta.glob(
  [
    '/src/ui/components/**/*.theme.ts',
    '/src/ui/components/**/*.variants.ts',
    '/src/ui/components/popover/themes.ts',
  ],
  { eager: true },
) as Record<string, Record<string, unknown>>;

const classesOf = (fn: ThemeFn, props: Record<string, unknown>): string[] => {
  try {
    const result = fn(props);

    if (typeof result === 'string') return [result];

    if (result && typeof result === 'object') {
      return Object.values(result)
        .filter((slot): slot is () => string => typeof slot === 'function')
        .map((slot) => slot());
    }

    return [];
  } catch {
    return [];
  }
};

const definitionsOf = (path: string): ThemeDefinition[] => {
  const parsed = parseDefinitions(rawThemes[path] ?? '');
  const module = themeModules[path] ?? {};

  return [...parsed]
    .map(([name, entries]) => ({ name, entries, fn: module[name] }))
    .filter(
      (candidate): candidate is typeof candidate & { fn: ThemeFn } =>
        typeof candidate.fn === 'function',
    )
    .map(({ name, entries, fn }) => ({
      name,
      entries,
      render: (key: string, value: string) => classesOf(fn, { [key]: value }),
    }));
};

export const themeFiles: ThemeFile[] = Object.keys(rawThemes)
  .sort()
  .map((path) => ({
    label: path.replace('/src/ui/components/', ''),
    definitions: definitionsOf(path),
  }))
  .filter((file) => file.definitions.length > 0);
