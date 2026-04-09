<script lang="ts">
  import { type TagInputVariants, tagInputVariants } from '@slink/feature/Tag';

  import { t } from '$lib/i18n';
  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagDisplayName } from '@slink/utils/tag';

  interface Props extends TagInputVariants {
    value?: string;
    placeholder?: string;
    searchTerm?: string;
    onSearchChange?: (value: string) => void;
    onEnter?: () => void;
    onEscape?: () => void;
    onBackspace?: () => void;
    onArrowDown?: () => void;
    onArrowUp?: () => void;
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
    placeholder = 'tag.selector.search_or_add_placeholder',
    disabled = false,
    searchTerm = $bindable(),
    onSearchChange,
    onEnter,
    onEscape,
    onBackspace,
    onArrowDown,
    onArrowUp,
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
    } else if (e.key === 'ArrowDown') {
      e.preventDefault();
      onArrowDown?.();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      onArrowUp?.();
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
      placeholder={$t('tag.input.child_tag_name_placeholder')}
      onkeydown={handleKeydown}
      oninput={handleChildInput}
      {onfocus}
      {onblur}
    />
    <button
      type="button"
      class="flex-shrink-0 p-1 hover:bg-muted rounded transition-colors"
      onclick={onCancelChild}
      title={$t('tag.input.cancel_child_creation')}
    >
      <Icon icon="ph:x" class="h-3 w-3" />
    </button>
  </div>
{:else}
  <input
    bind:this={ref}
    bind:value={inputValue}
    class={tagInputVariants({ size, variant, disabled })}
    placeholder={$t(placeholder)}
    {disabled}
    autocomplete="off"
    oninput={handleInput}
    onkeydown={handleKeydown}
    {onfocus}
    {onblur}
  />
{/if}
