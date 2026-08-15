<script lang="ts">
  import CheckIcon from '@lucide/svelte/icons/check';
  import MinusIcon from '@lucide/svelte/icons/minus';
  import { Checkbox as CheckboxPrimitive } from 'bits-ui';

  import { type WithoutChildrenOrChild, cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    checked = $bindable(false),
    indeterminate = $bindable(false),
    class: className,
    ...restProps
  }: WithoutChildrenOrChild<CheckboxPrimitive.RootProps> = $props();
</script>

<CheckboxPrimitive.Root
  bind:ref
  data-slot="checkbox"
  class={cn(
    'border-input dark:bg-input/30 data-[state=checked]:bg-foreground-solid data-[state=checked]:text-on-foreground-solid dark:data-[state=checked]:bg-foreground-solid data-[state=checked]:border-foreground-solid focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-danger/20 dark:aria-invalid:ring-danger/40 aria-invalid:border-danger shadow-xs peer flex size-4 shrink-0 items-center justify-center rounded-[4px] border outline-none transition-shadow focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50',
    className,
  )}
  bind:checked
  bind:indeterminate
  {...restProps}
>
  {#snippet children({ checked, indeterminate })}
    <div data-slot="checkbox-indicator" class="text-current transition-none">
      {#if checked}
        <CheckIcon class="size-3.5" />
      {:else if indeterminate}
        <MinusIcon class="size-3.5" />
      {/if}
    </div>
  {/snippet}
</CheckboxPrimitive.Root>
