export enum UserStatus {
  Active = 'active',
  Inactive = 'inactive',
  Suspended = 'suspended',
  Banned = 'banned',
  Deleted = 'deleted',
}

export enum UserRole {
  Admin = 'ROLE_ADMIN',
}

export type User = {
  id: string;
  email: string | null;
  displayName: string;
  username: string | null;
  roles: string[];
  status?: UserStatus;
};
