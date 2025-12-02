<script lang="ts">
  import { HashtagText } from '@slink/feature/Text';

  import type { HashtagVariant } from '../HashtagText/HashtagText.theme';

  interface Props extends HashtagVariant {
    content: string;
    isDeleted?: boolean;
    class?: string;
    deletedClass?: string;
    onBeforeNavigate?: () => void;
  }

  let {
    content,
    isDeleted = false,
    class: className = '',
    deletedClass = 'italic opacity-60',
    variant = 'secondary',
    size = 'md',
    rounded = 'md',
    onBeforeNavigate,
  }: Props = $props();
</script>

{#if isDeleted}
  <span class="{className} {deletedClass}">This comment has been deleted</span>
{:else}
  <HashtagText
    text={content}
    class={className}
    {variant}
    {size}
    {rounded}
    {onBeforeNavigate}
  />
{/if}
