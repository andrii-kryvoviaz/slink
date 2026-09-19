<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';
  import * as Timeline from '@slink/ui/components/timeline';

  import { notificationEntry } from '../NotificationEntry/NotificationEntry.theme';

  interface Props {
    count?: number;
    class?: string;
  }

  let { count = 6, class: customClass = '' }: Props = $props();

  const theme = notificationEntry();
</script>

<Timeline.Root class={customClass}>
  <Timeline.Group>
    {#snippet label()}
      <Skeleton width="48px" height="12px" rounded="md" />
    {/snippet}
    {#each { length: count }}
      <div class={theme.row()}>
        <Skeleton width="40px" height="40px" rounded="lg" />
        <Skeleton width="75%" height="14px" rounded="md" />
        <div class={theme.aside()}>
          <Skeleton width="40px" height="12px" rounded="md" />
        </div>
      </div>
    {/each}
  </Timeline.Group>
</Timeline.Root>
