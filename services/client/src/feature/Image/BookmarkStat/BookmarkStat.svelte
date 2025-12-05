<script lang="ts">
  import Icon from '@iconify/svelte';

  import {
    type BookmarkStatVariant,
    bookmarkStatContainerTheme,
    bookmarkStatIconTheme,
    bookmarkStatIconWrapperTheme,
    bookmarkStatLabelTheme,
    bookmarkStatValueTheme,
  } from './BookmarkStat.theme';

  interface Props {
    count: number;
    variant?: BookmarkStatVariant;
    href?: string;
    showChevron?: boolean;
  }

  let { count, variant = 'card', href, showChevron = false }: Props = $props();

  const label = $derived(count === 1 ? 'time' : 'times');
</script>

{#snippet content()}
  {#if variant !== 'compact' && variant !== 'overlay'}
    <div class={bookmarkStatIconWrapperTheme({ variant })}>
      <Icon
        icon="ph:bookmark-simple-fill"
        class={bookmarkStatIconTheme({ variant })}
      />
    </div>
    <div class="flex flex-col min-w-0 flex-1">
      <span class={bookmarkStatLabelTheme({ variant })}> Bookmarked </span>
      <span class={bookmarkStatValueTheme({ variant })}>
        {count}
        {label}
      </span>
    </div>
  {:else}
    <Icon
      icon="ph:bookmark-simple-fill"
      class={bookmarkStatIconTheme({ variant })}
    />
    <span class={bookmarkStatValueTheme({ variant })}>
      {count}
    </span>
  {/if}
  {#if showChevron}
    <Icon
      icon="lucide:chevron-right"
      class="h-5 w-5 text-gray-400 dark:text-gray-500"
    />
  {/if}
{/snippet}

{#if count > 0}
  {#if href}
    <a {href} class={bookmarkStatContainerTheme({ variant })}>
      {@render content()}
    </a>
  {:else}
    <div class={bookmarkStatContainerTheme({ variant })}>
      {@render content()}
    </div>
  {/if}
{/if}
