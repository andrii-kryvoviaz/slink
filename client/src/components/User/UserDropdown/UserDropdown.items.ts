import type { UserRole } from '@slink/lib/auth/Type/UserRole';

type UserDropdownGroup = {
  title: string;
  access: UserRole[];
  items: UserDropdownItem[];
  badge?: string;
  hidden?: boolean;
};

export type UserDropdownItem = {
  title: string;
  access: string[];
  icon: string;
  link: string;
  target?: string;
  isForm?: boolean;
  state?: 'active' | 'inactive' | 'hidden';
  badge?: string;
};

export const UserDropdownItems: UserDropdownGroup[] = [
  {
    title: 'Account',
    access: ['USER_ROLE'],
    items: [
      {
        title: 'View Profile',
        access: ['USER_ROLE', 'ADMIN_ROLE'],
        icon: 'ph:user',
        link: '/profile',
        state: 'active',
      },
      {
        title: 'Upload History',
        access: ['USER_ROLE', 'ADMIN_ROLE'],
        icon: 'material-symbols-light:history',
        link: '/history',
        state: 'active',
      },
    ],
  },
  {
    title: 'Admin',
    access: ['ADMIN_ROLE'],
    hidden: true,
    items: [
      {
        title: 'Dashboard',
        access: ['ADMIN_ROLE'],
        icon: 'solar:graph-line-duotone',
        link: '/admin',
        state: 'hidden',
      },
      {
        title: 'Settings',
        access: ['ADMIN_ROLE'],
        icon: 'mingcute:settings-7-line',
        link: '/admin/settings',
        state: 'hidden',
      },
    ],
  },
  {
    title: 'System',
    access: ['USER_ROLE', 'ADMIN_ROLE'],
    items: [
      {
        title: 'About',
        access: ['USER_ROLE', 'ADMIN_ROLE'],
        icon: 'material-symbols-light:conversion-path',
        link: '/about',
        state: 'hidden',
      },
      {
        title: 'View on GitHub',
        access: ['USER_ROLE', 'ADMIN_ROLE'],
        link: 'https://github.com/andrii-kryvoviaz/slink',
        icon: 'mdi:github',
        state: 'active',
        target: '_blank',
      },
      {
        title: 'Help',
        access: ['USER_ROLE', 'ADMIN_ROLE'],
        icon: 'fluent:chat-help-20-regular',
        link: '/help/faq',
        state: 'active',
      },
    ],
  },
  {
    title: 'Auth',
    access: ['USER_ROLE', 'ADMIN_ROLE'],
    items: [
      {
        title: 'Logout',
        access: ['USER_ROLE', 'ADMIN_ROLE'],
        icon: 'solar:logout-line-duotone',
        link: '/profile/logout',
        state: 'active',
        isForm: true,
      },
    ],
  },
];
