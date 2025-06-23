<script lang="ts">
  import { browser } from '$app/environment';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { useToastManager } from '@slink/utils/ui/toast.svelte';

  import { ToastItem, ToastMessage } from '@slink/components/UI/Toast';

  const toastManager = useToastManager();
  const flyOptions = { x: -300, y: 0, duration: 400 };
</script>

{#if browser}
  <div
    class="toast-container bottom-left fixed bottom-0 left-0 z-50 flex select-none flex-col gap-4 px-6 pb-6 sm:w-full sm:max-w-md"
  >
    {#each toastManager.textToasts as { id, timer, icon, iconColor, message, type }}
      <div
        in:fly={flyOptions}
        out:fade
        onmouseenter={timer?.pause}
        onmouseleave={timer?.resume}
        role="alert"
      >
        <ToastItem variant={type} rounded="sm">
          <ToastMessage removeToast={() => toastManager.remove(id)}>
            {#snippet messageIcon()}
              <Icon
                icon={icon || 'mdi:information-outline'}
                class="relative top-[-2px] mr-1 inline-block text-2xl {iconColor}"
              />
            {/snippet}

            <span>{@html message}</span>
          </ToastMessage>
        </ToastItem>
      </div>
    {/each}

    {#each toastManager.componentToasts as { id, timer, component: ToastComponent, props }}
      <div
        in:fly={flyOptions}
        out:fade
        onmouseenter={timer?.pause}
        onmouseleave={timer?.resume}
        role="alertdialog"
        tabindex="0"
      >
        <ToastItem variant="component" size="none" rounded="none">
          <ToastComponent {...props} />
        </ToastItem>
      </div>
    {/each}
  </div>
{/if}
