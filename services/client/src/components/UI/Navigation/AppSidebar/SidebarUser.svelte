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
    onItemClick?: () => void;
  }

  let { user, collapsed, onItemClick }: Props = $props();
</script>

<Dropdown variant="invisible" size="xs" class="w-full">
  {#snippet trigger()}
    <div
      class={className(
        AppSidebarUserSection({ collapsed }),
        'cursor-pointer overflow-hidden relative',
      )}
    >
      <UserAvatar
        size="sm"
        variant="default"
        class={AppSidebarUserAvatar()}
        {user}
      />

      <div
        class={className(
          AppSidebarUserInfo({ collapsed }),
          'absolute left-12 right-8 top-1/2 -translate-y-1/2 min-w-0 text-left',
        )}
      >
        <div class="text-sm font-medium text-foreground truncate w-full">
          {user.displayName || 'User'}
        </div>
        {#if user.email}
          <div
            class="text-xs text-muted-foreground truncate w-full"
            title={user.email}
          >
            {user.email}
          </div>
        {/if}
      </div>

      <div
        class={className(
          'absolute right-2 top-1/2 -translate-y-1/2 transition-all duration-300',
          collapsed
            ? 'opacity-0 pointer-events-none scale-0'
            : 'opacity-100 scale-100',
        )}
      >
        <Icon
          icon="ph:dots-three-vertical"
          class="h-4 w-4 text-muted-foreground group-hover:text-foreground transition-colors duration-200"
        />
      </div>
    </div>
  {/snippet}

  <DropdownGroup>
    <DropdownItem>
      {#snippet icon()}
        <Icon icon="ph:user" class="h-4 w-4" />
      {/snippet}
      <a href="/profile" class="flex w-full" onclick={onItemClick}>Profile</a>
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
        <button
          type="submit"
          class="flex w-full text-left"
          onclick={onItemClick}
        >
          Sign out
        </button>
      </form>
    </DropdownItem>
  </DropdownGroup>
</Dropdown>
