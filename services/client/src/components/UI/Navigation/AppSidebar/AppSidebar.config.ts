import type { AppSidebarGroup } from './AppSidebar.types';

export const createAppSidebarItems = (options?: {
  showAdmin?: boolean;
  showSystemItems?: boolean;
  customGroups?: AppSidebarGroup[];
}): AppSidebarGroup[] => {
  const {
    showAdmin = false,
    showSystemItems = true,
    customGroups = [],
  } = options || {};

  const baseGroups: AppSidebarGroup[] = [
    {
      id: 'main',
      items: [
        {
          id: 'explore',
          title: 'Explore',
          icon: 'ph:compass',
          href: '/explore',
        },
        {
          id: 'history',
          title: 'History',
          icon: 'ph:clock-counter-clockwise',
          href: '/history',
        },
      ],
    },
  ];

  if (showAdmin) {
    baseGroups.push({
      id: 'admin',
      title: 'Administration',
      items: [
        {
          id: 'dashboard',
          title: 'Dashboard',
          icon: 'ph:chart-line',
          href: '/admin/dashboard',
        },
        {
          id: 'users',
          title: 'Users',
          icon: 'ph:users',
          href: '/admin/user',
        },
        {
          id: 'admin-settings',
          title: 'Settings',
          icon: 'ph:gear-fine-light',
          href: '/admin/settings',
        },
      ],
      roles: ['ROLE_ADMIN'],
    });
  }

  if (showSystemItems) {
    baseGroups.push({
      id: 'system',
      title: 'System',
      items: [
        {
          id: 'help',
          title: 'Help & FAQ',
          icon: 'ph:question',
          href: '/help/faq',
        },
        {
          id: 'github',
          title: 'GitHub',
          icon: 'ph:github-logo',
          href: 'https://github.com/andrii-kryvoviaz/slink',
        },
      ],
    });
  }

  return [...baseGroups, ...customGroups];
};
