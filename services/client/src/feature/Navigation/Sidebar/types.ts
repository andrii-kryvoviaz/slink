export interface NavMainItem {
  title: string;
  url: string;
  icon?: unknown;
  iconName?: string;
  isActive?: boolean;
  items: NavSubItem[];
}

export interface NavSubItem {
  title: string;
  url: string;
}

export interface NavProject {
  name: string;
  url: string;
  icon: unknown;
}

export interface NavTeam {
  name: string;
  logo: unknown;
  plan: string;
}

export interface NavUser {
  name: string;
  email: string;
  avatar: string;
}

export interface SidebarData {
  navMain: NavMainItem[];
}

export interface SidebarConfig {
  showAdmin?: boolean;
  showSystemItems?: boolean;
  showUploadItem?: boolean;
  showUserItems?: boolean;
}

export interface AppSidebarItem {
  id: string;
  title: string;
  icon: string;
  href?: string;
  hidden?: boolean;
  roles?: string[];
  children?: AppSidebarItem[];
}

export interface AppSidebarGroup {
  id: string;
  title?: string;
  items: AppSidebarItem[];
  roles?: string[];
  hidden?: boolean;
}
