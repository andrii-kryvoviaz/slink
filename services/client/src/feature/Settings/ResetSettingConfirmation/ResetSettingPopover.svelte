<script lang="ts">
  import Icon from '@iconify/svelte';

  interface Props {
    close: () => void;
    confirm: () => void;
    name: string;
    displayValue: string;
    currentValue?: string;
  }

  let { close, confirm, name, displayValue, currentValue }: Props = $props();

  const handleConfirm = () => {
    confirm();
  };

  const handleCancel = () => {
    close();
  };
</script>

<div class="w-72 max-w-[calc(100vw-2rem)] space-y-4 p-1">
  <div class="flex items-center gap-3">
    <div
      class="flex h-8 w-8 items-center justify-center rounded-lg bg-info-subtle border border-info-border/50"
    >
      <Icon icon="lucide:rotate-cw" class="h-4 w-4 text-info" />
    </div>
    <div>
      <h3 class="text-sm font-semibold text-foreground">Reset Setting</h3>
      <p class="text-xs text-muted-foreground">Restore default value</p>
    </div>
  </div>

  <div class="space-y-3">
    <p class="text-sm text-muted-foreground leading-relaxed">
      {#if currentValue && currentValue !== displayValue}
        Reset <span class="font-medium text-foreground">"{name}"</span> from current
        value to default?
      {:else}
        Reset <span class="font-medium text-foreground">"{name}"</span> to its default
        value?
      {/if}
    </p>

    <div class="flex items-center justify-center">
      <div
        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-muted-subtle border border-border"
      >
        {#if currentValue && currentValue !== displayValue}
          <span class="font-mono text-sm text-muted-foreground">
            {currentValue}
          </span>
          <Icon
            icon="lucide:arrow-right"
            class="h-3 w-3 text-foreground-subtle"
          />
        {/if}
        <span class="font-mono text-sm font-medium text-foreground">
          {displayValue}
        </span>
      </div>
    </div>
  </div>

  <div class="flex gap-2 pt-2">
    <button
      type="button"
      class="flex-1 px-3 py-2 text-sm font-medium text-foreground-soft bg-card border border-border rounded-lg hover:bg-ghost-hover-strong transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-ring/20 active:scale-[0.98]"
      onclick={handleCancel}
    >
      Cancel
    </button>
    <button
      type="button"
      class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-info-surface-foreground bg-info-surface hover:bg-info-surface-strong active:bg-info-surface-strong rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-info/50 shadow-sm hover:shadow-md active:scale-[0.98]"
      onclick={handleConfirm}
    >
      <Icon icon="lucide:rotate-cw" class="w-3.5 h-3.5 mr-1.5" />
      Reset
    </button>
  </div>
</div>
