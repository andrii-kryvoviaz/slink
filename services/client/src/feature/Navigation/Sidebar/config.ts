import { GITHUB } from '$lib/constants/app';
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
  showUserItems?: boolean;
  customGroups?: AppSidebarGroup[];
}): AppSidebarGroup[] => {
  const {
    showAdmin = false,
    showSystemItems = true,
    showUploadItem = false,
    showUserItems = false,
    customGroups = [],
  } = options || {};

  const baseGroups: AppSidebarGroup[] = [
    {
      id: 'main',
      items: [
        {
          id: 'explore',
          title: 'navigation.sidebar.items.explore',
          icon: 'ph:compass',
          href: '/explore',
        },
        ...(showUploadItem
          ? [
              {
                id: 'upload',
                title: 'navigation.sidebar.items.upload',
                icon: 'ph:plus',
                href: '/upload',
              },
            ]
          : []),
        ...(showUserItems
          ? [
              {
                id: 'history',
                title: 'navigation.sidebar.items.history',
                icon: 'ph:clock-counter-clockwise',
                href: '/history',
              },
              {
                id: 'tags',
                title: 'navigation.sidebar.items.tags',
                icon: 'ph:tag',
                href: '/tags',
              },
              {
                id: 'collections',
                title: 'navigation.sidebar.items.collections',
                icon: 'ph:folder',
                href: '/collections',
              },
              {
                id: 'bookmarks',
                title: 'navigation.sidebar.items.bookmarks',
                icon: 'ph:bookmark-simple',
                href: '/bookmarks',
              },
              {
                id: 'notifications',
                title: 'navigation.sidebar.items.notifications',
                icon: 'ph:bell',
                href: '/notifications',
              },
            ]
          : []),
      ],
    },
  ];

  if (showAdmin) {
    baseGroups.push({
      id: 'admin',
      title: 'navigation.sidebar.groups.admin',
      items: [
        {
          id: 'dashboard',
          title: 'navigation.sidebar.items.dashboard',
          icon: 'ph:chart-line',
          href: '/admin/dashboard',
        },
        {
          id: 'users',
          title: 'navigation.sidebar.items.users',
          icon: 'ph:users',
          href: '/admin/user',
        },
        {
          id: 'admin-settings',
          title: 'navigation.sidebar.items.settings',
          icon: 'ph:gear-fine-light',
          href: '/admin/settings',
          children: [
            {
              id: 'settings-image',
              title: 'navigation.sidebar.items.image_settings',
              icon: 'solar:gallery-linear',
              href: '/admin/settings/image',
            },
            {
              id: 'settings-storage',
              title: 'navigation.sidebar.items.storage_configuration',
              icon: 'solar:database-linear',
              href: '/admin/settings/storage',
            },
            {
              id: 'settings-security',
              title: 'navigation.sidebar.items.security_settings',
              icon: 'solar:shield-check-linear',
              href: '/admin/settings/security',
            },
            {
              id: 'settings-sso',
              title: 'navigation.sidebar.items.single_sign_on',
              icon: 'solar:key-linear',
              href: '/admin/settings/sso',
            },
          ],
        },
      ],
      roles: ['ROLE_ADMIN'],
    });
  }

  if (showSystemItems) {
    baseGroups.push({
      id: 'system',
      title: 'navigation.sidebar.groups.system',
      items: [
        {
          id: 'help',
          title: 'navigation.sidebar.items.help_faq',
          icon: 'ph:question',
          href: '/help/faq',
        },
        {
          id: 'github',
          title: 'navigation.sidebar.items.github',
          icon: 'ph:github-logo',
          href: `https://github.com/${GITHUB.REPO_OWNER}/${GITHUB.REPO_NAME}`,
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
    showUserItems = false,
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
          if (item.id === 'tags' && !showUserItems) return false;
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
    showUserItems: config?.showUserItems,
  });

  return {
    navMain: createNavMainFromAppSidebar(appSidebarGroups, config),
  };
};
