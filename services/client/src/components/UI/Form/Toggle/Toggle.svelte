<script lang="ts">
  import { type Snippet } from 'svelte';
  import { twMerge } from 'tailwind-merge';

  import type { HTMLInputAttributes } from 'svelte/elements';

  import { ToggleTheme } from '@slink/components/UI/Form/Toggle/Toggle.theme';
  import type { ToggleProps } from '@slink/components/UI/Form/Toggle/Toggle.types';

  interface Props extends Omit<HTMLInputAttributes, 'size'>, ToggleProps {
    class?: string;
    preIcon?: Snippet;
    postIcon?: Snippet;
    on?: {
      change: (checked: boolean) => void;
    };
  }

  let {
    variant = 'default',
    size = 'md',
    checked = $bindable(false),
    preIcon,
    postIcon,
    on,
    ...props
  }: Props = $props();

  let classes = $derived(
    twMerge(
      `${ToggleTheme({
        variant,
        size,
      })} ${props.class}`,
    ),
  );

  const handleChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    checked = target.checked;

    on?.change(checked);
  };
</script>

<label class="flex cursor-pointer items-center gap-2">
  {@render preIcon?.()}
  <input type="checkbox" class={classes} {checked} onchange={handleChange} />
  <input {...props} type="hidden" value={checked} />
  {@render postIcon?.()}
</label>
