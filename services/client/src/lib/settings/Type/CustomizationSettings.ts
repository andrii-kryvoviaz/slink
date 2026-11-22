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

export type CustomTheme = {
  id: string;
  name: string;
  light: ThemePalette;
  dark: ThemePalette;
  radius: number | null;
};

export type CustomizationSettings = {
  siteName: string;
  siteDescription: string;
  logoUrl: string;
  customCss: string;
  themeSelectorEnabled: boolean;
  defaultTheme: string;
  hiddenThemes: string[];
  themes: CustomTheme[];
};
