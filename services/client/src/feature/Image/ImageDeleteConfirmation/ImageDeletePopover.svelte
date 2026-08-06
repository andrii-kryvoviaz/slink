<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { Switch } from '@slink/ui/components/switch';

  import Icon from '@iconify/svelte';

  type ConfirmAction = {
    preserveOnDiskAfterDeletion: boolean;
  };

  interface Props {
    loading?: boolean;
    close: () => void;
    confirm: ({ preserveOnDiskAfterDeletion }: ConfirmAction) => void;
  }

  let { loading = false, close, confirm }: Props = $props();

  let preserveOnDiskAfterDeletion: boolean = $state(false);

  const handleConfirm = () => {
    confirm({ preserveOnDiskAfterDeletion });
  };

  const handleCancel = () => {
    close();
  };
</script>

<div class="w-xs max-w-screen space-y-4">
  <div class="flex items-center gap-3">
    <div
      class="flex h-10 w-10 items-center justify-center rounded-full bg-danger/15 border border-danger-border/40 shadow-sm flex-shrink-0"
    >
      <Icon icon="ph:image" class="h-5 w-5 text-danger" />
    </div>
    <div>
      <h3 class="text-sm font-semibold text-foreground">Delete Image</h3>
      <p class="text-xs text-muted-foreground">
        Image record will be permanently removed
      </p>
    </div>
  </div>

  <div class="bg-muted/50 rounded-xl p-4 border border-border/50">
    <label class="flex items-center justify-between cursor-pointer">
      <div class="flex items-center gap-3">
        <div>
          <span class="text-sm font-medium text-foreground">
            Remove from storage
          </span>
          <p class="text-xs text-muted-foreground">
            Permanently delete the file from storage
          </p>
        </div>
      </div>
      <Switch
        checked={!preserveOnDiskAfterDeletion}
        onCheckedChange={(checked) => (preserveOnDiskAfterDeletion = !checked)}
        disabled={loading}
      />
    </label>
  </div>

  <div class="flex gap-3 pt-2">
    <Button
      variant="glass"
      rounded="full"
      size="sm"
      onclick={handleCancel}
      class="flex-1"
      disabled={loading}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      rounded="full"
      size="sm"
      onclick={handleConfirm}
      justify="center"
      class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
      {loading}
    >
      {#snippet leftIcon()}
        <Icon icon="heroicons:trash" class="h-4 w-4" />
      {/snippet}
      Delete Image
    </Button>
  </div>
</div>
