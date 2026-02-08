<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import type { Snippet } from 'svelte';

  import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { scale } from 'svelte/transition';

  import {
    CopyContainerButtonTheme,
    CopyContainerInputTheme,
    CopyContainerTheme,
  } from './CopyContainer.theme';
  import type {
    CopyContainerSize,
    CopyContainerVariant,
  } from './CopyContainer.types';

  interface CopyState {
    isCopied: boolean;
    isLoading: boolean;
    copy: () => Promise<void>;
  }

  interface Props {
    value: string;
    delay?: number;
    placeholder?: string;
    copyButtonContent?: Snippet<[]>;
    size?: CopyContainerSize;
    variant?: CopyContainerVariant;
    isLoading?: boolean;
    onBeforeCopy?: () => Promise<string | void>;
    actionSlot?: Snippet<[CopyState]>;
  }

  let {
    value,
    delay = 2000,
    placeholder = 'Copy link...',
    copyButtonContent,
    size = 'md',
    variant = 'default',
    isLoading = false,
    onBeforeCopy,
    actionSlot,
  }: Props = $props();

  const isCopiedState = useAutoReset(delay);
  let inputElement: HTMLInputElement | undefined = $state();

  const resolveValue = async (): Promise<string> => {
    if (onBeforeCopy) {
      const result = await onBeforeCopy();
      if (result) {
        return result;
      }
    }
    return value;
  };

  const handleCopy = async () => {
    try {
      const textToCopy = await resolveValue();
      await navigator.clipboard.writeText(textToCopy);
      isCopiedState.trigger();
      inputElement?.select();
    } catch (error) {
      console.error('Failed to copy text:', error);
    }
  };

  let prevActive = false;
  $effect(() => {
    if (prevActive && !isCopiedState.active) {
      inputElement?.blur();
    }
    prevActive = isCopiedState.active;
  });

  const handleInputClick = () => {
    if (inputElement) {
      inputElement.select();
    }
  };

  const containerClasses = $derived(CopyContainerTheme({ variant, size }));
  const inputClasses = $derived(CopyContainerInputTheme({ variant, size }));
  const buttonClasses = $derived(CopyContainerButtonTheme({ size }));

  const copyState: CopyState = $derived({
    isCopied: isCopiedState.active,
    isLoading,
    copy: handleCopy,
  });
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
    <div class="shrink-0 pr-1">
      {#if actionSlot}
        {@render actionSlot(copyState)}
      {:else}
        <Button
          class={buttonClasses}
          variant="primary"
          size="xs"
          rounded="sm"
          disabled={isCopiedState.active || isLoading}
          onclick={handleCopy}
        >
          {#if isLoading}
            <div
              class="flex items-center gap-1.5"
              in:scale={{ duration: 150, easing: cubicOut }}
            >
              <Icon icon="lucide:loader-2" class="h-3.5 w-3.5 animate-spin" />
              <span>Signing...</span>
            </div>
          {:else if isCopiedState.active}
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
      {/if}
    </div>
  </div>
</div>
