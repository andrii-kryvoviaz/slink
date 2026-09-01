import { Theme } from '@slink/lib/settings/Settings.enums';

export interface ThemeDescriptor {
  name: Theme;
  label: string;
}

const themeLabels: Record<Theme, () => string> = {
  [Theme.DEFAULT]: () => 'Default',
  [Theme.NORD]: () => 'Nord',
  [Theme.CATPPUCCIN]: () => 'Catppuccin',
  [Theme.GRUVBOX]: () => 'Gruvbox',
  [Theme.ROSE_PINE]: () => 'Rosé Pine',
  [Theme.TOKYO_NIGHT]: () => 'Tokyo Night',
  [Theme.EVERFOREST]: () => 'Everforest',
  [Theme.MONOCHROME]: () => 'Monochrome',
};

export const themes: ThemeDescriptor[] = Object.values(Theme).map((name) => ({
  name,
  get label() {
    return themeLabels[name]();
  },
}));
