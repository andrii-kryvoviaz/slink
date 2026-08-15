<script lang="ts">
  import { BatchActionConfirmation } from '@slink/feature/Image';
  import { Button } from '@slink/ui/components/button';
  import { Overlay } from '@slink/ui/components/popover';
  import { Switch } from '@slink/ui/components/switch';

  import Icon from '@iconify/svelte';

  interface Props {
    selectedCount: number;
    loading?: boolean;
    onAction: (preserveOnDisk: boolean) => Promise<void>;
  }

  let { selectedCount, loading = false, onAction }: Props = $props();
  let open = $state(false);
  let preserveOnDisk = $state(false);

  const handle = async () => {
    await onAction(preserveOnDisk);
    open = false;
  };
</script>

<Overlay
  bind:open
  variant="floating"
  rounded="xl"
  contentProps={{ align: 'center', side: 'top', sideOffset: 8 }}
>
  {#snippet trigger()}
    <Button
      variant="danger"
      size="sm"
      rounded="full"
      class="gap-1.5"
      disabled={loading}
    >
      <Icon icon="heroicons:trash" class="w-4 h-4" />
      <span class="hidden sm:inline">Delete</span>
    </Button>
  {/snippet}
  <BatchActionConfirmation
    count={selectedCount}
    icon="ph:images"
    variant="danger"
    {loading}
    confirmVariant="danger"
    onConfirm={handle}
    onCancel={() => (open = false)}
  >
    {#snippet title()}Delete{/snippet}
    {#snippet description()}This action cannot be undone{/snippet}
    {#snippet confirmText()}Delete {selectedCount === 1
        ? 'Image'
        : 'Images'}{/snippet}
    <div class="bg-muted/50 rounded-xl p-4 border border-border/50">
      <label class="flex items-center justify-between cursor-pointer">
        <div class="flex items-center gap-3">
          <div>
            <span class="text-sm font-medium text-foreground">
              Remove from storage
            </span>
            <p class="text-xs text-foreground-muted">
              Permanently delete the files from storage
            </p>
          </div>
        </div>
        <Switch
          checked={!preserveOnDisk}
          onCheckedChange={(checked) => (preserveOnDisk = !checked)}
          disabled={loading}
        />
      </label>
    </div>
  </BatchActionConfirmation>
</Overlay>
