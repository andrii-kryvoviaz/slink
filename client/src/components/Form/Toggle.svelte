<script lang="ts">
  import { createEventDispatcher } from 'svelte';

  import type { HTMLInputAttributes } from 'svelte/elements';

  interface $$Props extends HTMLInputAttributes {}

  const inputClasses = `toggle theme-controller text-toggle-default checked:text-toggle-checked [--tglbg:rgb(var(--bg-toggle))] checked:[--tglbg:rgb(var(--bg-toggle-checked))] ${$$props.class}`;

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
    class={inputClasses}
    on:change={handleChange}
  />
  <slot name="post-icon" />
</label>
