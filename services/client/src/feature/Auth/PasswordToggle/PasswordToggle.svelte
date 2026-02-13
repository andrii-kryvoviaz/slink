<script lang="ts">
  import { Tooltip } from '@slink/ui/components/tooltip';
  import type { VariantProps } from 'class-variance-authority';

  import Icon from '@iconify/svelte';

  import {
    passwordToggleIconVariants,
    passwordToggleVariants,
  } from './PasswordToggle.theme';

  type IconSize = VariantProps<typeof passwordToggleIconVariants>['size'];

  interface Props {
    visible: boolean;
    onclick: () => void;
    size?: IconSize;
    class?: string;
  }

  let { visible, onclick, size = 'md', class: className }: Props = $props();
</script>

<div class="absolute inset-y-0 right-0 flex items-center">
  <Tooltip
    variant="subtle"
    size="xs"
    side="top"
    sideOffset={8}
    withArrow={false}
    triggerProps={{}}
  >
    {#snippet trigger()}
      <button
        type="button"
        {onclick}
        class="{passwordToggleVariants()} {className}"
      >
        {#if visible}
          <Icon
            icon="ph:eye-slash"
            class={passwordToggleIconVariants({ size })}
          />
        {:else}
          <Icon icon="ph:eye" class={passwordToggleIconVariants({ size })} />
        {/if}
      </button>
    {/snippet}

    {#if visible}
      Hide password
    {:else}
      Show password
    {/if}
  </Tooltip>
</div>
