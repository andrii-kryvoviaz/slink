<script lang="ts">
  import type { HTMLInputAttributes as BaseHTMLInputAttributes } from 'svelte/elements';

  import { Input, type InputProps } from '@slink/components/Form';

  type HTMLInputAttributes = Pick<
    BaseHTMLInputAttributes,
    'value' | 'name' | 'step'
  >;

  interface $$Props extends HTMLInputAttributes, InputProps {}

  export let value: $$Props['value'] = '';
  export let name: $$Props['name'] = '';
  export let step: $$Props['step'] = 1;

  enum SizeLiteral {
    k = 'KB',
    M = 'MB',
  }

  let numberValue: number = 0;
  let sizeLiteral: keyof typeof SizeLiteral = 'k';

  [, numberValue, sizeLiteral] = value.match(/(\d+)(\w+)/);

  $: value = `${numberValue}${sizeLiteral}`;
</script>

<Input
  type="number"
  min={0}
  {step}
  {name}
  bind:value={numberValue}
  class="pr-12"
>
  <span
    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-sm text-gray-500 dark:text-gray-400"
  >
    {SizeLiteral[sizeLiteral]}
  </span>
</Input>
