<script lang="ts">
  import { dismiss } from '$lib/utils/time/dismiss.svelte';
  import Icon from '@iconify/svelte';

  import type { ShareStatusKind } from '../share.theme';
  import { statusIconName, status as statusTheme } from '../share.theme';

  interface Props {
    kind: ShareStatusKind | null;
    title: string;
  }

  let { kind, title }: Props = $props();

  const saved = dismiss(() => kind === 'saved', 2000);

  const theme = $derived(
    statusTheme({
      kind: kind ?? undefined,
      spinning: kind === 'saving',
    }),
  );
</script>

{#if kind !== null && !saved.done}
  <span class={theme.line()} aria-live="polite" {title}>
    <Icon icon={statusIconName(kind)} class={theme.icon()} />
  </span>
{/if}
