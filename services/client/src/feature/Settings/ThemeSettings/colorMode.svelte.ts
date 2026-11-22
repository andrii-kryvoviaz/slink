import type { ColorMode } from '@slink/lib/settings/Type/CustomizationSettings';

const colorModeLabels: Record<ColorMode, () => string> = {
  both: () => 'Light and dark',
  light: () => 'Light only',
  dark: () => 'Dark only',
};

export const colorModeLabel = (mode: ColorMode): string =>
  colorModeLabels[mode]();

/** Built at render time, so the labels follow the active locale. */
export const colorModeItems = (): { value: ColorMode; label: string }[] =>
  (Object.keys(colorModeLabels) as ColorMode[]).map((value) => ({
    value,
    label: colorModeLabel(value),
  }));
