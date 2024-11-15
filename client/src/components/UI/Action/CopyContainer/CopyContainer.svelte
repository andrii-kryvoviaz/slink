<script lang="ts">
  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { Button, type ButtonVariant } from '@slink/components/UI/Action';

  export let value: string;
  export let delay: number = 2000;

  let isCopiedActive: boolean = false;
  let variant: ButtonVariant = 'primary';

  const handleCopy = async () => {
    await navigator.clipboard.writeText(value);

    isCopiedActive = true;

    setTimeout(() => {
      isCopiedActive = false;
    }, delay);
  };

  $: variant = isCopiedActive ? 'success' : 'primary';
</script>

<div class="flex w-full max-w-[25rem] items-center text-[0.7rem] xs:text-xs">
  <div
    class="flex w-full flex-row items-center justify-center rounded-full border border-button-default p-1"
  >
    <input
      class="flex-grow bg-transparent px-3 focus:outline-none"
      type="text"
      {value}
      readonly
    />
    <Button
      class="ml-2 min-w-[5rem] text-[0.7rem] xs:text-xs"
      {variant}
      size="xs"
      rounded="full"
      disabled={isCopiedActive}
      on:click={handleCopy}
    >
      {#if isCopiedActive}
        <div in:fly={{ duration: 300 }}>
          <slot name="copied-text">
            <Icon icon="mdi:check" class="h-4 w-4" />
          </slot>
        </div>
      {:else}
        <slot name="copy-text">Copy</slot>
      {/if}
    </Button>
  </div>
</div>
