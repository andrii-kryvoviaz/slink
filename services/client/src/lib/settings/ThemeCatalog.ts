import { Theme } from './Settings.enums';
import {
  type CustomTheme,
  type CustomizationSettings,
  themeColors,
} from './Type/CustomizationSettings';

type ThemingSettings = Pick<
  CustomizationSettings,
  'themeSelectorEnabled' | 'defaultTheme' | 'hiddenThemes' | 'themes'
>;

export const builtInThemes: readonly string[] = Object.values(Theme);

export const themeVariables = (
  theme: Pick<CustomTheme, 'light' | 'dark' | 'radius'>,
): string => {
  const colors = themeColors.map(
    (color) =>
      `--color-${color}:light-dark(${theme.light[color]},${theme.dark[color]})`,
  );
  const radius = theme.radius === null ? [] : [`--radius:${theme.radius}rem`];

  return [...colors, ...radius].join(';');
};

export class ThemeCatalog {
  readonly selectorEnabled: boolean;
  readonly custom: CustomTheme[];
  readonly ids: string[];
  readonly defaultTheme: string;

  constructor(settings?: Partial<ThemingSettings> | null) {
    const hidden = settings?.hiddenThemes ?? [];

    this.selectorEnabled = settings?.themeSelectorEnabled ?? true;
    this.custom = settings?.themes ?? [];
    this.ids = [
      ...new Set([...builtInThemes, ...this.custom.map(({ id }) => id)]),
    ].filter((id) => !hidden.includes(id));

    this.defaultTheme = this.ids.includes(settings?.defaultTheme ?? '')
      ? settings!.defaultTheme!
      : Theme.DEFAULT;
  }

  resolve(preference: unknown): string {
    if (
      this.selectorEnabled &&
      typeof preference === 'string' &&
      this.ids.includes(preference)
    ) {
      return preference;
    }

    return this.defaultTheme;
  }

  /**
   * An edited built-in theme gets its own selector, so that nothing from the
   * stock preset leaks into it.
   */
  selector(id: string): string {
    const edited =
      builtInThemes.includes(id) && this.custom.some((t) => t.id === id);

    return edited ? `edited:${id}` : id;
  }

  get css(): string {
    return this.custom
      .map(
        (theme) =>
          `[data-theme='${this.selector(theme.id)}']{${themeVariables(theme)}}`,
      )
      .join('\n');
  }
}
