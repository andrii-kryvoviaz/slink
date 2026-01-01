<script lang="ts">
  import { Tooltip } from '@slink/ui/components/tooltip';

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
    compact?: boolean;
    class?: string;
  }

  let {
    isPublic,
    variant = 'default',
    compact = false,
    class: className = '',
  }: Props = $props();

  const status: VisibilityStatus = $derived(isPublic ? 'public' : 'private');
  const icon = $derived(isPublic ? 'lucide:globe' : 'lucide:lock');
  const label = $derived(isPublic ? 'Public' : 'Private');
  const title = $derived(
    isPublic ? 'This image is publicly visible' : 'This image is private',
  );
</script>

{#if compact}
  <Tooltip side="top" size="xs" variant="dark">
    {#snippet trigger()}
      <span
        class="{visibilityBadgeContainerTheme({ status, variant })} {className}"
        role="status"
      >
        <Icon {icon} class={visibilityBadgeIconTheme({ variant })} />
      </span>
    {/snippet}
    {title}
  </Tooltip>
{:else}
  <span
    class="{visibilityBadgeContainerTheme({ status, variant })} {className}"
    role="status"
  >
    <Icon {icon} class={visibilityBadgeIconTheme({ variant })} />
    <span>{label}</span>
  </span>
{/if}
