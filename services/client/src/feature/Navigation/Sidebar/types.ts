export interface NavMainItem {
  title: string;
  url: string;
  icon?: any;
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
  icon: any;
}

export interface NavTeam {
  name: string;
  logo: any;
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
}
