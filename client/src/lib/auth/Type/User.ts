export enum UserStatus {
  Active = 'active',
  Inactive = 'inactive',
  Suspended = 'suspended',
  Banned = 'banned',
  Deleted = 'deleted',
}

export type User = {
  id: string;
  email: string;
  displayName: string;
  roles: string[];
  status?: UserStatus;
};
