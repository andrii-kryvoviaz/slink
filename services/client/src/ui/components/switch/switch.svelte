<script lang="ts">
  import { Switch as SwitchPrimitive } from 'bits-ui';

  import { type WithoutChildrenOrChild, cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    checked = $bindable(false),
    name,
    ...restProps
  }: WithoutChildrenOrChild<SwitchPrimitive.RootProps> & {
    name?: string;
  } = $props();
</script>

<SwitchPrimitive.Root
  bind:ref
  bind:checked
  data-slot="switch"
  class={cn(
    'data-[state=checked]:bg-primary data-[state=unchecked]:bg-switch-unchecked focus-visible:border-ring focus-visible:ring-ring/50 shadow-xs peer inline-flex h-[1.15rem] w-8 shrink-0 items-center rounded-full border border-transparent outline-none transition-all focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50',
    className,
  )}
  {...restProps}
>
  <SwitchPrimitive.Thumb
    data-slot="switch-thumb"
    class={cn(
      'bg-background dark:data-[state=unchecked]:bg-foreground dark:data-[state=checked]:bg-primary-foreground pointer-events-none block size-4 rounded-full ring-0 transition-transform data-[state=checked]:translate-x-[calc(100%-2px)] data-[state=unchecked]:translate-x-0',
    )}
  />
</SwitchPrimitive.Root>
{#if name}
  <input type="hidden" {name} value={checked ? 'true' : 'false'} />
{/if}
