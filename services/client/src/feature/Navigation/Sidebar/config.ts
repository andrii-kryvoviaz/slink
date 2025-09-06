import Icon from '@iconify/svelte';

import type {
  AppSidebarGroup,
  NavMainItem,
  SidebarConfig,
  SidebarData,
} from './types';

export const createAppSidebarItems = (options?: {
  showAdmin?: boolean;
  showSystemItems?: boolean;
  showUploadItem?: boolean;
  customGroups?: AppSidebarGroup[];
}): AppSidebarGroup[] => {
  const {
    showAdmin = false,
    showSystemItems = true,
    showUploadItem = false,
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
        ...(showUploadItem
          ? [
              {
                id: 'upload',
                title: 'Upload',
                icon: 'ph:plus',
                href: '/upload',
              },
            ]
          : []),
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

export const createNavMainFromAppSidebar = (
  groups: AppSidebarGroup[],
  config?: SidebarConfig,
): NavMainItem[] => {
  const {
    showAdmin = false,
    showSystemItems = true,
    showUploadItem = false,
  } = config || {};

  return groups
    .filter((group) => {
      if (group.id === 'admin' && !showAdmin) return false;
      if (group.id === 'system' && !showSystemItems) return false;
      return true;
    })
    .flatMap((group) =>
      group.items
        .filter((item) => {
          if (item.id === 'upload' && !showUploadItem) return false;
          return !item.hidden;
        })
        .map((item) => ({
          title: item.title,
          url: item.href || '#',
          icon: Icon,
          iconName: item.icon,
          isActive: false,
          items:
            item.children?.map((child) => ({
              title: child.title,
              url: child.href || '#',
            })) ?? [],
        })),
    );
};

export const createSidebarData = (config?: SidebarConfig): SidebarData => {
  const appSidebarGroups = createAppSidebarItems({
    showAdmin: config?.showAdmin,
    showSystemItems: config?.showSystemItems,
    showUploadItem: config?.showUploadItem,
  });

  return {
    navMain: createNavMainFromAppSidebar(appSidebarGroups, config),
  };
};
