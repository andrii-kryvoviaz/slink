<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';
  import { DropdownMenu } from 'bits-ui';

  import { enhance } from '$app/forms';
  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { className as cn } from '@slink/lib/utils/ui/className';

  import { UserAvatar } from '@slink/components/Feature/User';

  import {
    UserDropdownContent,
    UserDropdownItem,
    UserDropdownTrigger,
  } from './UserDropdown.theme';

  interface Props {
    user?: Partial<User>;
    className?: string;
  }

  let { user = {}, className = '' }: Props = $props();

  let open = $state(false);
</script>

<DropdownMenu.Root bind:open>
  <DropdownMenu.Trigger class={cn(UserDropdownTrigger(), className)}>
    <UserAvatar size="xs" variant="default" class="shrink-0" {user} />

    <span
      class="text-neutral-800 dark:text-neutral-200 font-medium max-w-[120px] truncate"
    >
      {user?.displayName || 'User'}
    </span>

    <Icon
      icon={open ? 'ph:caret-up-bold' : 'ph:caret-down-bold'}
      class="h-3 w-3 text-neutral-500 transition-transform duration-200 ml-auto shrink-0"
    />
  </DropdownMenu.Trigger>

  <DropdownMenu.Portal>
    <DropdownMenu.Content
      class={UserDropdownContent()}
      side="bottom"
      align="end"
      sideOffset={8}
      alignOffset={-4}
      avoidCollisions={true}
      collisionPadding={8}
    >
      {#snippet child({ wrapperProps, props: contentProps, open: isOpen })}
        {#if isOpen}
          <div {...wrapperProps}>
            <div {...contentProps} transition:fly={{ y: -8, duration: 200 }}>
              <DropdownMenu.Item
                class={UserDropdownItem({ variant: 'default' })}
              >
                <a href="/profile" class="flex w-full items-center gap-3">
                  <Icon
                    icon="ph:user"
                    class="h-4 w-4 text-neutral-500 dark:text-neutral-400"
                  />
                  <span class="flex-1">Account</span>
                </a>
              </DropdownMenu.Item>

              <div
                class="my-1 h-px bg-neutral-200/50 dark:bg-neutral-700/50"
              ></div>

              <form action="/profile/logout" method="POST" use:enhance>
                <DropdownMenu.Item
                  class={UserDropdownItem({ variant: 'destructive' })}
                >
                  <button type="submit" class="flex w-full items-center gap-3">
                    <Icon icon="ph:sign-out" class="h-4 w-4" />
                    <span class="flex-1">Sign out</span>
                  </button>
                </DropdownMenu.Item>
              </form>
            </div>
          </div>
        {/if}
      {/snippet}
    </DropdownMenu.Content>
  </DropdownMenu.Portal>
</DropdownMenu.Root>
