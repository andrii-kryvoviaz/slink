<script lang="ts">
  import Icon from '@iconify/svelte';
  import Toast from './Toast.svelte';
  import { toast } from '../../store/toast';

  let toasts = toast.list;
</script>

<div
  class="toast-container bottom-left absolute bottom-4 left-4 z-50 flex w-full max-w-xs select-none flex-col gap-3"
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

        <span>{message}</span>
      </Toast>
    </button>
  {/each}
</div>
