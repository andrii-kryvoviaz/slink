<script lang="ts">
  import { EmptyState } from '@slink/feature/Layout';
  import { Button } from '@slink/ui/components/button';
</script>

<div class="flex items-center justify-center min-h-[60vh] px-4">
  <EmptyState
    icon="ph:link-break-duotone"
    title="Share unavailable"
    description="The link you followed is no longer available or does not exist."
  >
    {#snippet action()}
      <Button variant="outline" rounded="full" size="sm" href="/">
        Take me home
      </Button>
    {/snippet}
  </EmptyState>
</div>
