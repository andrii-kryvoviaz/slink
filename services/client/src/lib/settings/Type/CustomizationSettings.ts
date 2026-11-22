export const themeColors = [
  'background',
  'foreground',
  'primary',
  'accent',
  'success',
  'warning',
  'danger',
  'info',
] as const;

export type ThemeColor = (typeof themeColors)[number];
export type ThemePalette = Record<ThemeColor, string>;

export type ColorMode = 'both' | 'light' | 'dark';

/**
 * `light` and `dark` are both null only for a built-in id, which then keeps the
 * stock colors of that theme.
 */
export type CustomTheme = {
  id: string;
  name: string;
  light: ThemePalette | null;
  dark: ThemePalette | null;
  radius: number | null;
  colorMode: ColorMode;
  css: string;
};

/** The editor always works on real colors. */
export type ThemeDraft = Omit<CustomTheme, 'light' | 'dark'> & {
  light: ThemePalette;
  dark: ThemePalette;
};

export type CustomizationSettings = {
  siteName: string;
  siteDescription: string;
  logoUrl: string;
  colorMode: ColorMode;
  themeSelectorEnabled: boolean;
  defaultTheme: string;
  hiddenThemes: string[];
  themes: CustomTheme[];
};
