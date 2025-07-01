import { createAppSidebarItems } from '@slink/components/UI/Navigation/AppSidebar/AppSidebar.config';
import type { AppSidebarGroup } from '@slink/components/UI/Navigation/AppSidebar/AppSidebar.types';

import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ locals, request }) => {
  const { settings, user } = locals;

  const userAgent = request.headers.get('user-agent') || '';

  const isAuthorized = (requiredRoles: string[] = ['ROLE_USER']): boolean => {
    if (!user?.roles) return false;
    return requiredRoles.some((role) => user.roles?.includes(role));
  };

  const sidebarGroups: AppSidebarGroup[] = user
    ? createAppSidebarItems({
        showAdmin: isAuthorized(['ROLE_ADMIN']),
        showSystemItems: true,
      })
        .map((group) => ({
          ...group,
          items: group.items.filter(
            (item) => !item.hidden && (!item.roles || isAuthorized(item.roles)),
          ),
        }))
        .filter(
          (group) =>
            !group.hidden &&
            group.items.length > 0 &&
            (!group.roles || isAuthorized(group.roles)),
        )
    : [];

  return {
    settings,
    user,
    userAgent,
    sidebarGroups,
  };
};
