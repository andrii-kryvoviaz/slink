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
  email: string;
  displayName: string;
  username: string;
  roles: string[];
  status?: UserStatus;
};
