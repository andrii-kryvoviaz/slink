import { Theme } from '@slink/lib/settings/Settings.enums';
import { localize } from '@slink/lib/utils/i18n';

export interface ThemeDescriptor {
  name: Theme;
  label: string;
}

export const themes: ThemeDescriptor[] = [
  {
    name: Theme.DEFAULT,
    get label() {
      return localize('Default');
    },
  },
  {
    name: Theme.NORD,
    get label() {
      return localize('Nord');
    },
  },
];
