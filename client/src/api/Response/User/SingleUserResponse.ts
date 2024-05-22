import type { UserRole } from '@slink/lib/auth/Type/UserRole';

export type SingleUserResponse = {
  id: string;
  email: string;
  displayName: string;
  createdAt: {
    formattedDate: string;
    timestamp: number;
  };
  updatedAt: null;
  status: string;
  roles: UserRole[];
};
