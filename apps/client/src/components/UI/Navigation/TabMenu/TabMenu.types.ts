import type { TabMenuTheme } from '@slink/components/Layout';
import type { VariantProps } from 'class-variance-authority';

export type TabMenuProps = VariantProps<typeof TabMenuTheme>;

export type TabMenuItemData = {
  key: string;
  active: boolean;
  href?: string;
  ref: HTMLElement;
};

export type TabMenuContext = {
  onRegister: (item: TabMenuItemData) => void;
  onSelect: (key: string) => void;
  onMouseEnter: (ikey: string) => void;
  onMouseLeave: (key: string) => void;
};
