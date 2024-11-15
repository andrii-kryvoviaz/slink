import type { VariantProps } from 'class-variance-authority';

import type { TabMenuTheme } from '@slink/components/Layout';

export type TabMenuProps = VariantProps<typeof TabMenuTheme>;

export type TabMenuItemData = {
  key: string;
  active: boolean;
  href: string | undefined;
  ref: HTMLElement;
};

export type TabMenuContext = {
  onRegister: (item: TabMenuItemData) => void;
  onSelect: (item: TabMenuItemData) => void;
  onMouseEnter: (item: TabMenuItemData) => void;
  onMouseLeave: (item: TabMenuItemData) => void;
};
