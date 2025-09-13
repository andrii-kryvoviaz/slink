import type { User } from './Type/User';
import { UserRole } from './Type/User';

export const hasRole = (
  user: User | null | undefined,
  role: string,
): boolean => {
  return user?.roles?.includes(role) ?? false;
};

export const hasAnyRole = (
  user: User | null | undefined,
  roles: string[],
): boolean => {
  if (!user?.roles) return false;
  return roles.some((role) => user.roles?.includes(role));
};

export const isAdmin = (user: User | null | undefined): boolean => {
  return hasRole(user, UserRole.Admin);
};

export const isAuthorized = (
  user: User | null | undefined,
  requiredRoles: string[] = ['ROLE_USER'],
): boolean => {
  return hasAnyRole(user, requiredRoles);
};
