<script lang="ts">
  import { browser } from '$app/environment';
  import Icon from '@iconify/svelte';

  import { toast } from '@slink/utils/ui/toast';

  import { Toast } from '@slink/components/Toast';

  let toasts = toast.list();
</script>

{#if browser}
  <div
    class="toast-container bottom-left absolute bottom-0 left-0 z-50 flex select-none flex-col gap-3 px-4 pb-4 sm:w-full sm:max-w-sm"
  >
    {#each $toasts as { id, timer, icon, iconColor, message }}
      <button on:mouseenter={timer.pause} on:mouseleave={timer.resume}>
        <Toast removeToast={() => toast.remove(id)}>
          <span slot="icon">
            <Icon
              icon={icon || 'mdi:information-outline'}
              class="relative top-[-2px] mr-1 inline-block text-2xl {iconColor}"
            />
          </span>

          <span>{@html message}</span>
        </Toast>
      </button>
    {/each}
  </div>
{/if}
