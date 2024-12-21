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
    access: ['ROLE_USER'],
    items: [
      {
        title: 'View Profile',
        access: ['ROLE_USER'],
        icon: 'ph:user',
        link: '/profile',
        state: 'active',
      },
      {
        title: 'Upload History',
        access: ['ROLE_USER'],
        icon: 'material-symbols-light:history',
        link: '/history',
        state: 'active',
      },
    ],
  },
  {
    title: 'Admin',
    access: ['ROLE_ADMIN'],
    items: [
      {
        title: 'Dashboard',
        access: ['ROLE_ADMIN'],
        icon: 'solar:graph-line-duotone',
        link: '/admin/dashboard',
        state: 'active',
      },
      {
        title: 'Settings',
        access: ['ROLE_ADMIN'],
        icon: 'mingcute:settings-7-line',
        link: '/admin/settings',
        state: 'active',
      },
    ],
  },
  {
    title: 'System',
    access: ['ROLE_USER'],
    items: [
      {
        title: 'About',
        access: ['ROLE_USER'],
        icon: 'material-symbols-light:conversion-path',
        link: '/about',
        state: 'hidden',
      },
      {
        title: 'View on GitHub',
        access: ['ROLE_USER'],
        link: 'https://github.com/andrii-kryvoviaz/slink',
        icon: 'mdi:github',
        state: 'active',
        target: '_blank',
      },
      {
        title: 'Help',
        access: ['ROLE_USER'],
        icon: 'fluent:chat-help-20-regular',
        link: '/help/faq',
        state: 'active',
      },
    ],
  },
  {
    title: 'Auth',
    access: ['ROLE_USER'],
    items: [
      {
        title: 'Logout',
        access: ['ROLE_USER'],
        icon: 'solar:logout-line-duotone',
        link: '/profile/logout',
        state: 'active',
        isForm: true,
      },
    ],
  },
];
