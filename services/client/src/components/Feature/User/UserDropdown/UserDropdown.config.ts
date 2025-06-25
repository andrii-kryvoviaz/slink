import type { UserDropdownGroup } from './UserDropdown.types';

export const defaultUserDropdownGroups: UserDropdownGroup[] = [
  {
    id: 'account',
    title: 'Account',
    items: [
      {
        id: 'profile',
        title: 'View Profile',
        icon: 'ph:user',
        href: '/profile',
      },
      {
        id: 'history',
        title: 'Upload History',
        icon: 'material-symbols-light:history',
        href: '/history',
      },
    ],
  },
  {
    id: 'admin',
    title: 'Administration',
    items: [
      {
        id: 'dashboard',
        title: 'Dashboard',
        icon: 'solar:graph-line-duotone',
        href: '/admin/dashboard',
      },
      {
        id: 'settings',
        title: 'Settings',
        icon: 'mingcute:settings-7-line',
        href: '/admin/settings',
      },
    ],
    hidden: true,
  },
  {
    id: 'system',
    title: 'System',
    items: [
      {
        id: 'about',
        title: 'About',
        icon: 'material-symbols-light:conversion-path',
        href: '/about',
        hidden: true,
      },
      {
        id: 'github',
        title: 'View on GitHub',
        icon: 'mdi:github',
        href: 'https://github.com/andrii-kryvoviaz/slink',
        target: '_blank',
      },
      {
        id: 'help',
        title: 'Help',
        icon: 'fluent:chat-help-20-regular',
        href: '/help/faq',
      },
    ],
  },
  {
    id: 'auth',
    title: 'Authentication',
    items: [
      {
        id: 'logout',
        title: 'Logout',
        icon: 'solar:logout-line-duotone',
        href: '/profile/logout',
        isForm: true,
        variant: 'destructive',
      },
    ],
  },
];
