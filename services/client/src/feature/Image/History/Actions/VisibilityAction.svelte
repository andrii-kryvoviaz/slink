<script lang="ts">
  import { BatchActionConfirmation } from '@slink/feature/Image';
  import { Button } from '@slink/ui/components/button';
  import { Overlay } from '@slink/ui/components/popover';

  import Icon from '@iconify/svelte';

  interface Props {
    selectedCount: number;
    loading?: boolean;
    onAction: (isPublic: boolean) => Promise<void>;
  }

  let { selectedCount, loading = false, onAction }: Props = $props();
  let open = $state(false);

  const handle = async (isPublic: boolean) => {
    await onAction(isPublic);
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
      variant="ghost"
      size="sm"
      rounded="full"
      class="gap-1.5"
      disabled={loading}
    >
      <Icon icon="lucide:eye" class="w-4 h-4" />
      <span class="hidden sm:inline">Visibility</span>
    </Button>
  {/snippet}
  <BatchActionConfirmation
    count={selectedCount}
    icon="lucide:eye"
    {loading}
    onCancel={() => (open = false)}
  >
    {#snippet title()}Change Visibility{/snippet}
    {#snippet description()}Set visibility for selected images{/snippet}
    {#snippet actions()}
      <Button
        variant="glass"
        rounded="full"
        size="sm"
        onclick={() => handle(true)}
        class="flex-1 gap-1.5"
        disabled={loading}
      >
        <Icon icon="lucide:eye" class="w-4 h-4" />
        Make Public
      </Button>
      <Button
        variant="glass"
        rounded="full"
        size="sm"
        onclick={() => handle(false)}
        class="flex-1 gap-1.5"
        disabled={loading}
      >
        <Icon icon="lucide:eye-off" class="w-4 h-4" />
        Make Private
      </Button>
    {/snippet}
  </BatchActionConfirmation>
</Overlay>
