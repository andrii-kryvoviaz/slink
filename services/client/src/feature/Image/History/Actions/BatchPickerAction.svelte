<script lang="ts">
  import { BatchActionConfirmation } from '@slink/feature/Image';
  import { Button } from '@slink/ui/components/button';
  import { Overlay } from '@slink/ui/components/popover';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  interface Props {
    icon: string;
    label: string;
    confirmLabel: string;
    selectedCount: number;
    pendingCount: number;
    loading?: boolean;
    onOpen?: () => void;
    onApply: () => void;
    children?: Snippet;
  }

  let {
    icon,
    label,
    confirmLabel,
    selectedCount,
    pendingCount,
    loading = false,
    onOpen,
    onApply,
    children,
  }: Props = $props();

  let open = $state(false);

  $effect(() => {
    if (open) onOpen?.();
  });

  const handleApply = () => {
    onApply();
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
      <Icon {icon} class="w-4 h-4" />
      <span class="hidden sm:inline">{label}</span>
    </Button>
  {/snippet}
  <BatchActionConfirmation
    count={selectedCount}
    {icon}
    {loading}
    onCancel={() => (open = false)}
  >
    {#snippet title()}{confirmLabel}{/snippet}
    {#snippet description()}Select items to apply{/snippet}
    {#if children}
      <div class="[&>*]:w-full">
        {@render children()}
      </div>
    {/if}
    {#snippet actions()}
      <Button
        variant="glass"
        rounded="full"
        size="sm"
        onclick={() => (open = false)}
        class="flex-1"
        disabled={loading}
      >
        Cancel
      </Button>
      <Button
        variant="default"
        rounded="full"
        size="sm"
        onclick={handleApply}
        class="flex-1 gap-1.5 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
        disabled={pendingCount === 0 || loading}
      >
        <Icon icon="lucide:check" class="h-4 w-4" />
        Apply
      </Button>
    {/snippet}
  </BatchActionConfirmation>
</Overlay>
