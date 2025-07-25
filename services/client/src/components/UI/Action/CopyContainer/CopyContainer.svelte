<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { scale } from 'svelte/transition';

  import { Button } from '@slink/components/UI/Action';

  import {
    CopyContainerButtonTheme,
    CopyContainerInputTheme,
    CopyContainerTheme,
  } from './CopyContainer.theme';
  import type {
    CopyContainerSize,
    CopyContainerVariant,
  } from './CopyContainer.types';

  interface Props {
    value: string;
    delay?: number;
    placeholder?: string;
    copyButtonContent?: Snippet<[]>;
    size?: CopyContainerSize;
    variant?: CopyContainerVariant;
  }

  let {
    value,
    delay = 2000,
    placeholder = 'Copy link...',
    copyButtonContent,
    size = 'md',
    variant = 'default',
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

  const containerClasses = $derived(CopyContainerTheme({ variant, size }));
  const inputClasses = $derived(CopyContainerInputTheme({ variant, size }));
  const buttonClasses = $derived(CopyContainerButtonTheme({ size }));
</script>

<div class="flex w-full items-center">
  <div class={containerClasses}>
    <div class="flex-1 min-w-0">
      <input
        bind:this={inputElement}
        class={inputClasses}
        type="text"
        {value}
        {placeholder}
        readonly
        onclick={handleInputClick}
      />
    </div>
    <div class="flex-shrink-0 pr-1">
      <Button
        class={buttonClasses}
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
