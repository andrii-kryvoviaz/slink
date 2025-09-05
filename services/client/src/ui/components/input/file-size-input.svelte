<script lang="ts">
  import { Input } from '@slink/ui/components/input';

  import type { HTMLInputAttributes } from 'svelte/elements';

  import type { ErrorList } from '@slink/api/Exceptions';

  import { parseFileSize } from '@slink/utils/string/parseFileSize';

  interface Props extends Pick<HTMLInputAttributes, 'value' | 'name' | 'step'> {
    label?: string;
    error?: string | ErrorList;
    size?: 'sm' | 'md' | 'lg';
    variant?: 'default' | 'error' | 'modern';
    rounded?: 'sm' | 'md' | 'lg';
    class?: string;
  }

  let { value = $bindable(), name, step = 1, ...props }: Props = $props();

  let inner = $derived({
    get parsed() {
      return parseFileSize(value);
    },

    get size() {
      return parseInt(value);
    },

    get unit() {
      return this.parsed.unit;
    },

    set size(newValue: number | string) {
      if (typeof newValue === 'string' && newValue) {
        newValue = parseInt(newValue);
      }

      if (newValue === null) {
        newValue = '';
      }

      value = `${newValue}${this.parsed.unitValue}`;
    },
  });
</script>

<Input
  type="number"
  min={0}
  {step}
  {name}
  bind:value={inner.size}
  error={props.error}
  class="pr-12"
>
  <span
    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-sm text-gray-500 dark:text-gray-400"
  >
    {inner.unit}
  </span>
</Input>
