import {
  type ThemeDraft,
  type ThemePalette,
  themeColors,
} from '@slink/lib/settings/Type/CustomizationSettings';

const maxIdLength = 40;

export const isHexColor = (value: string): boolean =>
  /^#[0-9a-f]{6}$/i.test(value);

export const uniqueThemeId = (name: string, taken: string[]): string => {
  const slug =
    name
      .normalize('NFKD')
      .toLowerCase()
      .replace(/[̀-ͯ]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .slice(0, maxIdLength)
      .replace(/^-+|-+$/g, '') || 'theme';

  let id = slug;

  for (let suffix = 2; taken.includes(id); suffix++) {
    id = `${slug}-${suffix}`;
  }

  return id;
};

const createHexConverter = () => {
  const canvas = document.createElement('canvas');
  canvas.width = canvas.height = 1;
  const context = canvas.getContext('2d', { willReadFrequently: true })!;

  return (color: string): string => {
    context.fillStyle = color;
    context.fillRect(0, 0, 1, 1);
    const [r, g, b] = context.getImageData(0, 0, 1, 1).data;

    return `#${[r, g, b].map((c) => c.toString(16).padStart(2, '0')).join('')}`;
  };
};

/** The build turns light-dark() into this pair of variables. */
const schemeStyles = {
  light:
    'color-scheme:light;--lightningcss-light:initial;--lightningcss-dark: ;',
  dark: 'color-scheme:dark;--lightningcss-light: ;--lightningcss-dark:initial;',
};

const readPalette = (id: string, scheme: 'light' | 'dark'): ThemePalette => {
  const probe = document.createElement('span');
  probe.dataset.theme = id;
  probe.style.cssText = schemeStyles[scheme];
  probe.hidden = true;
  document.body.append(probe);

  const toHex = createHexConverter();
  const computed = getComputedStyle(probe);
  const palette = Object.fromEntries(
    themeColors.map((color) => [
      color,
      toHex(computed.getPropertyValue(`--color-${color}`)),
    ]),
  ) as ThemePalette;

  probe.remove();

  return palette;
};

/** Reads a built-in theme back from its stylesheet. */
export const readTheme = (id: string): ThemeDraft => ({
  id,
  name: '',
  light: readPalette(id, 'light'),
  dark: readPalette(id, 'dark'),
  radius: null,
  css: '',
});

export const samePalette = (a: ThemePalette, b: ThemePalette): boolean =>
  themeColors.every(
    (color) => a[color].toLowerCase() === b[color].toLowerCase(),
  );
