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
