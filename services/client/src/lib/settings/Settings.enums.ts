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

export const resolveMode = (value: unknown): Mode => {
  const modes: string[] = Object.values(Mode);

  if (typeof value === 'string' && modes.includes(value)) {
    return value as Mode;
  }

  return Mode.DARK;
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
