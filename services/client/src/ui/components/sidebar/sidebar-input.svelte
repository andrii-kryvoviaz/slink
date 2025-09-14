<script lang="ts">
  import { Root as Input } from '@slink/ui/components/input/index.js';

  import type {
    HTMLInputAttributes,
    HTMLInputTypeAttribute,
  } from 'svelte/elements';

  import { type WithElementRef, cn } from '@slink/utils/ui/index.js';

  type InputType = Exclude<HTMLInputTypeAttribute, 'file'>;

  type Props = WithElementRef<
    Omit<HTMLInputAttributes, 'type'> &
      (
        | { type: 'file'; files?: FileList }
        | { type?: InputType; files?: undefined }
      ),
    HTMLInputElement
  >;

  let {
    ref = $bindable(null),
    value = $bindable(''),
    class: className,
    ...restProps
  }: Props = $props();
</script>

<Input
  bind:ref
  bind:value
  data-slot="sidebar-input"
  data-sidebar="input"
  class={cn('bg-background h-8 w-full shadow-none', className)}
  {...restProps}
/>
