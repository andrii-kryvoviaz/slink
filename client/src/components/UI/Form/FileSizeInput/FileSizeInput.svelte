<script lang="ts">
  import type { HTMLInputAttributes as BaseHTMLInputAttributes } from 'svelte/elements';

  import { parseFileSize } from '@slink/utils/string/parseFileSize';

  import { Input, type InputProps } from '@slink/components/UI/Form';

  type HTMLInputAttributes = Pick<
    BaseHTMLInputAttributes,
    'value' | 'name' | 'step'
  >;

  interface $$Props extends HTMLInputAttributes, InputProps {}

  export let value: $$Props['value'] = '';
  export let name: $$Props['name'] = '';
  export let step: $$Props['step'] = 1;

  $: inner = {
    get parsed() {
      return parseFileSize(value);
    },

    get size() {
      return this.parsed.size;
    },

    get unit() {
      return this.parsed.unit;
    },

    set size(newValue) {
      value = `${newValue}${this.parsed.unitValue}`;
    },
  };
</script>

<Input
  type="number"
  min={0}
  {step}
  {name}
  bind:value={inner.size}
  class="pr-12"
>
  <span
    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-sm text-gray-500 dark:text-gray-400"
  >
    {inner.unit}
  </span>
</Input>
