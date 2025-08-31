<script lang="ts">
  import { Input, type InputProps } from '@slink/legacy/UI/Form';
  import type { Snippet } from 'svelte';

  import type { HTMLInputAttributes } from 'svelte/elements';

  interface Props
    extends Pick<HTMLInputAttributes, 'value' | 'name' | 'step' | 'min'>,
      InputProps {
    class?: string;
    children?: Snippet;
  }

  let {
    value = $bindable(),
    name,
    step = 1,
    min = 0,
    children,
    ...props
  }: Props = $props();

  let inner = $derived({
    get value() {
      return value;
    },

    set value(newValue) {
      if (newValue === null) {
        value = null;
        return;
      }

      value = parseInt(newValue, 10);
    },
  });
</script>

<Input
  type="number"
  {min}
  {step}
  {name}
  bind:value={inner.value}
  class={props.class}
  error={props.error}
>
  {@render children?.()}
</Input>
