<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { Button, type ButtonVariant } from '@slink/components/UI/Action';

  interface Props {
    value: string;
    delay?: number;
    copyButtonContent?: Snippet<[]>;
  }

  let { value, delay = 2000, copyButtonContent }: Props = $props();

  let isCopiedActive: boolean = $state(false);
  let variant: ButtonVariant = $derived(isCopiedActive ? 'success' : 'primary');

  const handleCopy = async () => {
    await navigator.clipboard.writeText(value);

    isCopiedActive = true;

    setTimeout(() => {
      isCopiedActive = false;
    }, delay);
  };
</script>

<div class="flex w-full max-w-[25rem] items-center text-[0.7rem] xs:text-xs">
  <div
    class="flex w-full flex-row items-center justify-center rounded-full border border-bc-button-default p-1"
  >
    <input
      class="grow bg-transparent px-3 focus:outline-hidden"
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
      onclick={handleCopy}
    >
      {#if isCopiedActive}
        <div in:fly={{ duration: 300 }}>
          {#if copyButtonContent}
            {@render copyButtonContent?.()}
          {:else}
            <Icon icon="mdi:check" class="h-4 w-4" />
          {/if}
        </div>
      {:else if copyButtonContent}
        {@render copyButtonContent?.()}
      {:else}
        Copy
      {/if}
    </Button>
  </div>
</div>
