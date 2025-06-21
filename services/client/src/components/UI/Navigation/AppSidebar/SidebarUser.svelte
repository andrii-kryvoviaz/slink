<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';

  import { enhance } from '$app/forms';
  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  import { UserAvatar } from '@slink/components/Feature/User';
  import {
    Dropdown,
    DropdownGroup,
    DropdownItem,
  } from '@slink/components/UI/Action';

  import {
    AppSidebarUserAvatar,
    AppSidebarUserInfo,
    AppSidebarUserSection,
  } from './AppSidebar.theme';

  interface Props {
    user: Partial<User>;
    collapsed: boolean;
  }

  let { user, collapsed }: Props = $props();
</script>

<Dropdown variant="invisible" size="xs">
  {#snippet trigger()}
    <div
      class={className(AppSidebarUserSection({ collapsed }), 'cursor-pointer')}
    >
      <UserAvatar
        size="sm"
        variant="default"
        class={AppSidebarUserAvatar()}
        {user}
      />

      <div class={AppSidebarUserInfo({ collapsed })}>
        <div class="text-sm font-medium text-foreground truncate text-left">
          {user.displayName || 'User'}
        </div>
        {#if user.email}
          <div class="text-xs text-muted-foreground truncate">
            {user.email}
          </div>
        {/if}
      </div>

      {#if !collapsed}
        <Icon
          icon="ph:dots-three-vertical"
          class="h-4 w-4 text-muted-foreground group-hover:text-foreground transition-colors duration-200 ml-auto shrink-0"
        />
      {/if}
    </div>
  {/snippet}

  <DropdownGroup>
    <DropdownItem>
      {#snippet icon()}
        <Icon icon="ph:user" class="h-4 w-4" />
      {/snippet}
      <a href="/profile" class="flex w-full">Profile</a>
    </DropdownItem>
  </DropdownGroup>

  <DropdownGroup>
    <DropdownItem danger={true}>
      {#snippet icon()}
        <Icon icon="ph:sign-out" class="h-4 w-4" />
      {/snippet}
      <form
        action="/profile/logout"
        method="POST"
        use:enhance
        class="flex w-full"
      >
        <button type="submit" class="flex w-full text-left"> Sign out </button>
      </form>
    </DropdownItem>
  </DropdownGroup>
</Dropdown>
