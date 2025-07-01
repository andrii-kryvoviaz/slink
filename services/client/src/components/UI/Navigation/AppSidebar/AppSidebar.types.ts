import type { VariantProps } from 'class-variance-authority';

import type { User } from '@slink/lib/auth/Type/User';

import type { AppSidebarTheme } from './AppSidebar.theme';

export type AppSidebarVariant = VariantProps<typeof AppSidebarTheme>;
export type AppSidebarThemeProps = VariantProps<typeof AppSidebarTheme>;
export type AppSidebarSize = AppSidebarThemeProps['size'];

export interface AppSidebarItem {
  id: string;
  title: string;
  icon: string;
  href?: string;
  action?: () => void;
  badge?: {
    text: string;
    variant?: 'primary' | 'secondary' | 'success' | 'warning' | 'danger';
  };
  children?: AppSidebarItem[];
  roles?: string[];
  hidden?: boolean;
  disabled?: boolean;
}

export interface AppSidebarGroup {
  id: string;
  title?: string;
  items: AppSidebarItem[];
  roles?: string[];
  hidden?: boolean;
  collapsible?: boolean;
}

export interface AppSidebarProps extends AppSidebarVariant {
  user?: Partial<User>;
  groups?: AppSidebarGroup[];
  collapsed?: boolean;
  className?: string;
  onItemSelect?: (item: AppSidebarItem) => void;
  onCollapseToggle?: (collapsed: boolean) => void;
}

export interface AppSidebarUser {
  displayName?: string;
  email?: string;
  avatar?: string;
  roles?: string[];
}
