<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import { twMerge } from 'tailwind-merge';

  import type { HTMLInputAttributes } from 'svelte/elements';

  import { ToggleTheme } from '@slink/components/Form/Toggle/Toggle.theme';
  import type { ToggleProps } from '@slink/components/Form/Toggle/Toggle.types';

  interface $$Props extends Omit<HTMLInputAttributes, 'size'>, ToggleProps {
    class?: string;
  }

  export let variant: $$Props['variant'] = 'default';
  export let size: $$Props['size'] = 'md';

  $: classes = twMerge(
    `${ToggleTheme({
      variant,
      size,
    })} ${$$props.class}`
  );

  delete $$props.size;

  const dispatch = createEventDispatcher<{ change: boolean }>();
  const handleChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    dispatch('change', target.checked);
  };
</script>

<label class="flex cursor-pointer items-center gap-2">
  <slot name="pre-icon" />
  <input
    {...$$props}
    type="checkbox"
    class={classes}
    on:change={handleChange}
  />
  <slot name="post-icon" />
</label>
