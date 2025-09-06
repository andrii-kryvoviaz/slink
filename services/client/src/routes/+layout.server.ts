import { createAppSidebarItems } from '@slink/feature/Navigation/Sidebar/config';
import type { AppSidebarGroup } from '@slink/feature/Navigation/Sidebar/types';

import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ locals, request }) => {
  const { settings, globalSettings, user } = locals;

  const userAgent = request.headers.get('user-agent') || '';

  const isAuthorized = (requiredRoles: string[] = ['ROLE_USER']): boolean => {
    if (!user?.roles) return false;
    return requiredRoles.some((role) => user.roles?.includes(role));
  };

  const sidebarGroups: AppSidebarGroup[] = createAppSidebarItems({
    showAdmin: user ? isAuthorized(['ROLE_ADMIN']) : false,
    showSystemItems: true,
    showUploadItem: !user && !!globalSettings?.access?.allowGuestUploads,
  })
    .map((group) => ({
      ...group,
      items: group.items.filter(
        (item) =>
          !item.hidden && (!item.roles || (user && isAuthorized(item.roles))),
      ),
    }))
    .filter(
      (group) =>
        !group.hidden &&
        group.items.length > 0 &&
        (!group.roles || (user && isAuthorized(group.roles))),
    );

  return {
    settings,
    globalSettings,
    user,
    userAgent,
    sidebarGroups,
  };
};
