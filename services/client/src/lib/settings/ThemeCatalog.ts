import { Mode, Theme } from './Settings.enums';
import {
  type ColorMode,
  type CustomTheme,
  type CustomizationSettings,
  themeColors,
} from './Type/CustomizationSettings';

type ThemingSettings = Pick<
  CustomizationSettings,
  | 'colorMode'
  | 'themeSelectorEnabled'
  | 'defaultTheme'
  | 'hiddenThemes'
  | 'themes'
>;

export const builtInThemes: readonly string[] = Object.values(Theme);

export const themeVariables = (
  theme: Pick<CustomTheme, 'light' | 'dark' | 'radius'>,
): string => {
  const { light, dark } = theme;
  const colors =
    light && dark
      ? themeColors.map(
          (color) =>
            `--color-${color}:light-dark(${light[color]},${dark[color]})`,
        )
      : [];
  const radius = theme.radius === null ? [] : [`--radius:${theme.radius}rem`];

  return [...colors, ...radius].join(';');
};

export class ThemeCatalog {
  readonly selectorEnabled: boolean;
  readonly colorMode: ColorMode;
  readonly custom: CustomTheme[];
  readonly ids: string[];
  readonly defaultTheme: string;

  constructor(settings?: Partial<ThemingSettings> | null) {
    const hidden = settings?.hiddenThemes ?? [];

    this.selectorEnabled = settings?.themeSelectorEnabled ?? true;
    this.colorMode = settings?.colorMode ?? 'both';
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
   * A built-in theme with changed colors gets its own selector, so that nothing
   * from the stock preset leaks into it.
   */
  selector(id: string): string {
    const edited =
      builtInThemes.includes(id) &&
      this.custom.some(
        (theme) =>
          theme.id === id && theme.light !== null && theme.dark !== null,
      );

    return edited ? `edited:${id}` : id;
  }

  /**
   * The mode that is forced for everyone, or 'both'. A site setting other than
   * 'both' wins; otherwise the active theme decides. `id` is the active theme.
   */
  colorModeFor(id: string): ColorMode {
    if (this.colorMode !== 'both') {
      return this.colorMode;
    }

    return this.custom.find((theme) => theme.id === id)?.colorMode ?? 'both';
  }

  /** The forced mode of the active theme, or the user's own mode. */
  modeFor(id: string, mode: Mode): Mode {
    const forced = this.colorModeFor(id);

    if (forced === 'both') {
      return mode;
    }

    return forced === 'dark' ? Mode.DARK : Mode.LIGHT;
  }

  /** The custom CSS of a theme, applied only while that theme is active. */
  customCssFor(id: string): string {
    return this.custom.find((theme) => theme.id === id)?.css ?? '';
  }

  get css(): string {
    return this.custom
      .map((theme) => {
        const variables = themeVariables(theme);

        return variables
          ? `[data-theme='${this.selector(theme.id)}']{${variables}}`
          : '';
      })
      .filter(Boolean)
      .join('\n');
  }
}
