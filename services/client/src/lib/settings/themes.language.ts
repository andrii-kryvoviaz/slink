import { Theme } from '@slink/lib/settings/Settings.enums';
import { localize } from '@slink/lib/utils/i18n';

export interface ThemeDescriptor {
  name: Theme;
  label: string;
}

const themeLabels: Record<Theme, () => string> = {
  [Theme.DEFAULT]: () => localize('Default'),
  [Theme.NORD]: () => localize('Nord'),
};

export const themes: ThemeDescriptor[] = Object.values(Theme).map((name) => ({
  name,
  get label() {
    return themeLabels[name]();
  },
}));
