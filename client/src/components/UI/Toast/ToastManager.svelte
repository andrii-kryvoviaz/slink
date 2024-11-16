<script lang="ts">
  import { browser } from '$app/environment';
  import Icon from '@iconify/svelte';
  import { derived } from 'svelte/store';
  import { fade, fly } from 'svelte/transition';

  import { toast } from '@slink/utils/ui/toast';

  import { ToastItem, ToastMessage } from '@slink/components/UI/Toast';

  const store = toast.list();
  const flyOptions = { x: -200, y: 0 };

  $: componentToasts = derived(store, ($toasts) => {
    return $toasts.filter((toast) => toast.type === 'component');
  });

  $: textToasts = derived(store, ($toasts) => {
    return $toasts.filter((toast) => toast.type !== 'component');
  });
</script>

{#if browser}
  <div
    class="toast-container bottom-left absolute bottom-0 left-0 z-50 flex select-none flex-col gap-3 px-4 pb-4 sm:w-full sm:max-w-sm"
  >
    {#each $textToasts as { id, timer, icon, iconColor, message }}
      <div
        in:fly={flyOptions}
        out:fade
        on:mouseenter={timer?.pause}
        on:mouseleave={timer?.resume}
        role="alert"
      >
        <ToastItem>
          <ToastMessage removeToast={() => toast.remove(id)}>
            <span slot="icon">
              <Icon
                icon={icon || 'mdi:information-outline'}
                class="relative top-[-2px] mr-1 inline-block text-2xl {iconColor}"
              />
            </span>

            <span>{@html message}</span>
          </ToastMessage>
        </ToastItem>
      </div>
    {/each}

    {#each $componentToasts as { id, timer, component, props }}
      <div
        in:fly={flyOptions}
        out:fade
        on:mouseenter={timer?.pause}
        on:mouseleave={timer?.resume}
        role="alertdialog"
      >
        <ToastItem>
          <svelte:component this={component} {...props} />
        </ToastItem>
      </div>
    {/each}
  </div>
{/if}
