<script lang="ts">
  import Icon from '@iconify/svelte';

  import {
    type VisibilityBadgeVariant,
    type VisibilityStatus,
    visibilityBadgeContainerTheme,
    visibilityBadgeIconTheme,
  } from './VisibilityBadge.theme';

  interface Props {
    isPublic: boolean;
    variant?: VisibilityBadgeVariant;
    showLabel?: boolean;
    class?: string;
  }

  let {
    isPublic,
    variant = 'default',
    showLabel = true,
    class: className = '',
  }: Props = $props();

  const status: VisibilityStatus = $derived(isPublic ? 'public' : 'private');
  const icon = $derived(isPublic ? 'lucide:globe' : 'lucide:lock');
  const label = $derived(isPublic ? 'Public' : 'Private');
  const title = $derived(
    isPublic ? 'This image is publicly visible' : 'This image is private',
  );
</script>

<span
  class="{visibilityBadgeContainerTheme({ status, variant })} {className}"
  {title}
  role="status"
>
  <Icon {icon} class={visibilityBadgeIconTheme({ variant })} />
  {#if showLabel}
    <span>{label}</span>
  {/if}
</span>
