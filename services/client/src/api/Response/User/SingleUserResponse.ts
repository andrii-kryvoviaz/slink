import type { UserRole } from '@slink/lib/auth/Type/UserRole';

import { UserStatus } from '@slink/lib/auth/Type/User';

export type SingleUserResponse = {
  id: string;
  email: string;
  username: string;
  displayName: string;
  createdAt: {
    formattedDate: string;
    timestamp: number;
  };
  updatedAt: null;
  status: UserStatus;
  roles: UserRole[];
};
