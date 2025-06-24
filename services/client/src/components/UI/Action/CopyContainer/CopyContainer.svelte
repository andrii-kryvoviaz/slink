<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { scale } from 'svelte/transition';

  import { Button } from '@slink/components/UI/Action';

  interface Props {
    value: string;
    delay?: number;
    placeholder?: string;
    copyButtonContent?: Snippet<[]>;
    size?: 'sm' | 'md' | 'lg';
  }

  let {
    value,
    delay = 2000,
    placeholder = 'Copy link...',
    copyButtonContent,
    size = 'md',
  }: Props = $props();

  let isCopiedActive: boolean = $state(false);
  let inputElement: HTMLInputElement | undefined = $state();

  const handleCopy = async () => {
    try {
      await navigator.clipboard.writeText(value);
      isCopiedActive = true;

      if (inputElement) {
        inputElement.select();
      }

      setTimeout(() => {
        isCopiedActive = false;
        if (inputElement) {
          inputElement.blur();
        }
      }, delay);
    } catch (error) {
      console.error('Failed to copy text:', error);
    }
  };

  const handleInputClick = () => {
    if (inputElement) {
      inputElement.select();
    }
  };

  const sizeClasses = {
    sm: {
      container: 'max-w-xs text-xs',
      input: 'px-3 py-2 text-xs',
      button: 'text-xs min-w-[4rem]',
    },
    md: {
      container: 'max-w-md text-sm',
      input: 'px-4 py-2.5 text-sm',
      button: 'text-sm min-w-[5rem]',
    },
    lg: {
      container: 'max-w-lg text-base',
      input: 'px-4 py-3 text-base',
      button: 'text-base min-w-[6rem]',
    },
  };

  const currentSize = $derived(sizeClasses[size]);
</script>

<div class="flex w-full items-center {currentSize.container}">
  <div
    class="group relative flex w-full items-center rounded-lg border border-gray-200/50 dark:border-gray-700/30 bg-gray-50/80 dark:bg-gray-800/50 transition-all duration-200 hover:bg-gray-100/50 dark:hover:bg-gray-800/70 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-gray-200/50 dark:focus-within:border-gray-700/30"
  >
    <div class="flex-1 min-w-0">
      <input
        bind:this={inputElement}
        class="w-full bg-transparent {currentSize.input} border-0 focus:outline-none focus:ring-0 font-mono text-gray-700 dark:text-gray-300 placeholder-gray-400"
        type="text"
        {value}
        {placeholder}
        readonly
        onclick={handleInputClick}
      />
    </div>
    <div class="flex-shrink-0 pr-1">
      <Button
        class="{currentSize.button} transition-all duration-200"
        variant="primary"
        size="xs"
        disabled={isCopiedActive}
        onclick={handleCopy}
      >
        {#if isCopiedActive}
          <div
            class="flex items-center gap-1.5"
            in:scale={{ duration: 150, easing: cubicOut }}
          >
            {#if copyButtonContent}
              {@render copyButtonContent()}
            {:else}
              <Icon icon="lucide:check" class="h-3.5 w-3.5" />
              <span>Copied</span>
            {/if}
          </div>
        {:else}
          <div class="flex items-center gap-1.5">
            {#if copyButtonContent}
              {@render copyButtonContent()}
            {:else}
              <Icon icon="lucide:copy" class="h-3.5 w-3.5" />
              <span>Copy</span>
            {/if}
          </div>
        {/if}
      </Button>
    </div>
  </div>
</div>
