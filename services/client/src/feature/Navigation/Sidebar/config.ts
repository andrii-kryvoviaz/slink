import ChartLineIcon from '@lucide/svelte/icons/chart-line';
import ClockIcon from '@lucide/svelte/icons/clock';
import CompassIcon from '@lucide/svelte/icons/compass';
import GithubIcon from '@lucide/svelte/icons/github';
import HelpCircleIcon from '@lucide/svelte/icons/help-circle';
import PlusIcon from '@lucide/svelte/icons/plus';
import Settings2Icon from '@lucide/svelte/icons/settings-2';
import UsersIcon from '@lucide/svelte/icons/users';
import type { AppSidebarGroup } from '@slink/feature/Navigation/AppSidebar/AppSidebar.types';

import type { NavMainItem, SidebarConfig, SidebarData } from './types';

const iconMap: Record<string, any> = {
  'ph:compass': CompassIcon,
  'ph:plus': PlusIcon,
  'ph:clock-counter-clockwise': ClockIcon,
  'ph:chart-line': ChartLineIcon,
  'ph:users': UsersIcon,
  'ph:gear-fine-light': Settings2Icon,
  'ph:question': HelpCircleIcon,
  'ph:github-logo': GithubIcon,
};

export const createAppSidebarItems = (
  config?: SidebarConfig,
): AppSidebarGroup[] => {
  const {
    showAdmin = false,
    showSystemItems = true,
    showUploadItem = false,
  } = config || {};

  const baseGroups: AppSidebarGroup[] = [
    {
      id: 'main',
      items: [
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

  return baseGroups;
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
          icon: iconMap[item.icon],
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
  const appSidebarGroups = createAppSidebarItems(config);

  return {
    navMain: createNavMainFromAppSidebar(appSidebarGroups, config),
  };
};
