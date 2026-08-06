export enum Mode {
  LIGHT = 'light',
  DARK = 'dark',
}

export enum Theme {
  DEFAULT = 'default',
  NORD = 'nord',
}

export const resolveTheme = (value: unknown): Theme => {
  const themes: string[] = Object.values(Theme);

  if (typeof value === 'string' && themes.includes(value)) {
    return value as Theme;
  }

  return Theme.DEFAULT;
};

export enum Locale {
  EN = 'en',
  DE = 'de',
  ES = 'es',
  FR = 'fr',
  IT = 'it',
  PL = 'pl',
  UK = 'uk',
  JA = 'ja',
  ZH = 'zh',
}
