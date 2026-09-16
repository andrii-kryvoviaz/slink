<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { Copyable } from '@slink/ui/components/copyable';
  import { InputGroup } from '@slink/ui/components/input-group';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';
  import type { Attachment } from 'svelte/attachments';
  import { cubicOut } from 'svelte/easing';
  import type { MouseEventHandler } from 'svelte/elements';
  import { scale } from 'svelte/transition';

  import {
    CopyContainerButtonTheme,
    CopyContainerInputTheme,
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
    fluid?: boolean;
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
    fluid = false,
    isLoading = false,
    onBeforeCopy,
    actionSlot,
  }: Props = $props();

  const resolveValue = async (): Promise<string> => {
    if (onBeforeCopy) {
      const result = await onBeforeCopy();
      if (result) {
        return result;
      }
    }
    return value;
  };

  const syncSelection =
    (copied: boolean): Attachment<HTMLInputElement> =>
    (input) => {
      if (copied) {
        input.select();
        return;
      }

      input.blur();
    };

  const handleInputClick: MouseEventHandler<HTMLInputElement> = (event) => {
    event.currentTarget.select();
  };

  const inputClasses = $derived(
    CopyContainerInputTheme({ variant, size, mono: true }),
  );
  const buttonClasses = $derived(CopyContainerButtonTheme({ size }));
</script>

<div class="flex w-full items-center">
  <Copyable text={resolveValue} {delay}>
    {#snippet children({ copied, copy })}
      <InputGroup {variant} {size} {fluid}>
        <div class="flex-1 min-w-0">
          <input
            {@attach syncSelection(copied)}
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
            {@render actionSlot({ isCopied: copied, isLoading, copy })}
          {:else}
            <Button
              class={buttonClasses}
              variant="primary"
              size="xs"
              rounded="sm"
              disabled={copied || isLoading}
              onclick={copy}
            >
              {#if isLoading}
                <div
                  class="flex items-center gap-1.5"
                  in:scale={{ duration: 150, easing: cubicOut }}
                >
                  <Icon
                    icon="lucide:loader-2"
                    class="h-3.5 w-3.5 animate-spin"
                  />
                  <span>Signing...</span>
                </div>
              {:else if copied}
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
      </InputGroup>
    {/snippet}
  </Copyable>
</div>
