// export type UserStatus = 'active' | 'inactive' | 'suspended' | 'banned';

export enum UserStatus {
  Active = 'active',
  Inactive = 'inactive',
  Suspended = 'suspended',
  Banned = 'banned',
}

export type User = {
  id: string;
  email: string;
  displayName: string;
  roles: string[];
  status?: UserStatus;
};
