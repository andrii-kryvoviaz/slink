export enum Mode {
  LIGHT = 'light',
  DARK = 'dark',
  SYSTEM = 'system',
}

export enum Theme {
  DEFAULT = 'default',
  NORD = 'nord',
  CATPPUCCIN = 'catppuccin',
  GRUVBOX = 'gruvbox',
  ROSE_PINE = 'rose-pine',
  TOKYO_NIGHT = 'tokyo-night',
  EVERFOREST = 'everforest',
  MONOCHROME = 'monochrome',
}

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

const resolveEnum = <T extends Record<string, string>>(
  values: T,
  value: unknown,
  fallback: T[keyof T],
): T[keyof T] => {
  if (typeof value === 'string' && Object.values(values).includes(value)) {
    return value as T[keyof T];
  }

  return fallback;
};

export const resolveTheme = (value: unknown): Theme =>
  resolveEnum(Theme, value, Theme.DEFAULT);

export const resolveMode = (value: unknown): Mode =>
  resolveEnum(Mode, value, Mode.SYSTEM);

export const resolveLocale = (value: unknown): Locale =>
  resolveEnum(Locale, value, Locale.EN);
