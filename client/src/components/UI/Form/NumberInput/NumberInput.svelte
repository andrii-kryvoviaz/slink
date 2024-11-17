<script lang="ts">
  import type { HTMLInputAttributes as BaseHTMLInputAttributes } from 'svelte/elements';

  import { Input, type InputProps } from '@slink/components/UI/Form';

  type HTMLInputAttributes = Pick<
    BaseHTMLInputAttributes,
    'value' | 'name' | 'step' | 'min'
  >;

  interface $$Props extends HTMLInputAttributes, InputProps {}

  export let value: $$Props['value'] = '';
  export let name: $$Props['name'] = '';
  export let step: $$Props['step'] = 1;
  export let min: $$Props['min'] = 0;

  $: inner = {
    get value() {
      return value;
    },

    set value(newValue) {
      value = parseInt(newValue, 10);
    },
  };
</script>

<Input type="number" {min} {step} {name} bind:value={inner.value} />
