<script lang="ts">
  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagDisplayName } from '@slink/utils/tag';

  import { type TagInputVariants, tagInputVariants } from './TagInput.theme';

  interface Props extends TagInputVariants {
    value?: string;
    placeholder?: string;
    searchTerm?: string;
    onSearchChange?: (value: string) => void;
    onEnter?: () => void;
    onEscape?: () => void;
    onBackspace?: () => void;
    creatingChildFor?: Tag | null;
    childTagName?: string;
    onCancelChild?: () => void;
    onCreateChild?: () => void;
    childRef?: HTMLInputElement;
    oninput?: (event: Event) => void;
    onkeydown?: (event: KeyboardEvent) => void;
    onfocus?: () => void;
    onblur?: () => void;
    ref?: HTMLInputElement;
  }

  let {
    value = $bindable(),
    placeholder = 'Search or add tags...',
    disabled = false,
    searchTerm = $bindable(),
    onSearchChange,
    onEnter,
    onEscape,
    onBackspace,
    creatingChildFor = null,
    childTagName = $bindable(),
    onCancelChild,
    onCreateChild,
    childRef = $bindable(),
    oninput,
    onkeydown,
    onfocus,
    onblur,
    ref = $bindable(),
    variant = 'default',
    size = 'md',
  }: Props = $props();

  let inputValue = $derived(searchTerm !== undefined ? searchTerm : value);

  const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const newValue = target.value;

    if (searchTerm !== undefined) {
      searchTerm = newValue;
      onSearchChange?.(newValue);
    } else {
      value = newValue;
    }

    oninput?.(event);
  };

  const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      if (creatingChildFor && onCreateChild) {
        onCreateChild();
      } else if (onEnter) {
        onEnter();
      }
    } else if (e.key === 'Escape') {
      if (creatingChildFor && onCancelChild) {
        onCancelChild();
      } else if (onEscape) {
        onEscape();
      }
    } else if (
      e.key === 'Backspace' &&
      !inputValue &&
      !creatingChildFor &&
      onBackspace
    ) {
      onBackspace();
    }

    onkeydown?.(e);
  };

  const handleChildInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    childTagName = target.value;
  };
</script>

{#if creatingChildFor}
  <div class="flex items-center gap-2 text-sm text-muted-foreground">
    <Icon icon="ph:plus" class="h-3 w-3 flex-shrink-0" />
    <span class="text-xs whitespace-nowrap flex-shrink-0">
      {getTagDisplayName(creatingChildFor)} ›
    </span>
    <input
      bind:this={childRef}
      bind:value={childTagName}
      class={tagInputVariants({ size, variant, disabled })}
      placeholder="Child tag name..."
      onkeydown={handleKeydown}
      oninput={handleChildInput}
      {onfocus}
      {onblur}
    />
    <button
      type="button"
      class="flex-shrink-0 p-1 hover:bg-muted rounded transition-colors"
      onclick={onCancelChild}
      title="Cancel child tag creation"
    >
      <Icon icon="ph:x" class="h-3 w-3" />
    </button>
  </div>
{:else}
  <input
    bind:this={ref}
    bind:value={inputValue}
    class={tagInputVariants({ size, variant, disabled })}
    {placeholder}
    {disabled}
    autocomplete="off"
    oninput={handleInput}
    onkeydown={handleKeydown}
    {onfocus}
    {onblur}
  />
{/if}
