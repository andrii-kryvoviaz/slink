import { createAppSidebarItems } from '@slink/feature/Navigation/Sidebar/config';
import type { AppSidebarGroup } from '@slink/feature/Navigation/Sidebar/types';

import { ApiClient } from '@slink/api/Client';

import { isAdmin, isAuthorized } from '@slink/lib/auth/utils';

import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ locals, request, fetch }) => {
  const { settings, globalSettings, user } = locals;

  const userAgent = request.headers.get('user-agent') || '';

  const sidebarGroups: AppSidebarGroup[] = createAppSidebarItems({
    showAdmin: isAdmin(user),
    showSystemItems: true,
    showUploadItem: !user && !!globalSettings?.access?.allowGuestUploads,
    showUserItems: !!user,
  })
    .map((group) => ({
      ...group,
      items: group.items.filter(
        (item) =>
          !item.hidden &&
          (!item.roles || (user && isAuthorized(user, item.roles))),
      ),
    }))
    .filter(
      (group) =>
        !group.hidden &&
        group.items.length > 0 &&
        (!group.roles || (user && isAuthorized(user, group.roles))),
    );

  const storageUsage = isAdmin(user)
    ? await ApiClient.use(fetch).storage.getUsage()
    : null;

  return {
    settings,
    globalSettings,
    user,
    userAgent,
    sidebarGroups,
    storageUsage,
  };
};
