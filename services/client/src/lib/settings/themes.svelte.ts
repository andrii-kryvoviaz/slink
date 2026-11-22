import { Theme } from '@slink/lib/settings/Settings.enums';
import type { ThemeCatalog } from '@slink/lib/settings/ThemeCatalog';

export interface ThemeDescriptor {
  name: string;
  label: string;
}

const builtInLabels: Record<string, () => string> = {
  [Theme.DEFAULT]: () => 'Default',
  [Theme.NORD]: () => 'Nord',
  [Theme.CATPPUCCIN]: () => 'Catppuccin',
  [Theme.GRUVBOX]: () => 'Gruvbox',
  [Theme.ROSE_PINE]: () => 'Rosé Pine',
  [Theme.TOKYO_NIGHT]: () => 'Tokyo Night',
  [Theme.EVERFOREST]: () => 'Everforest',
  [Theme.MONOCHROME]: () => 'Monochrome',
};

export const builtInThemeLabel = (id: string): string =>
  builtInLabels[id]?.() ?? id;

export const themeLabel = (catalog: ThemeCatalog, id: string): string =>
  catalog.custom.find((theme) => theme.id === id)?.name ??
  builtInThemeLabel(id);

export const describeThemes = (catalog: ThemeCatalog): ThemeDescriptor[] =>
  catalog.ids.map((id) => ({ name: id, label: themeLabel(catalog, id) }));
