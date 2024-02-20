<script lang="ts">
  export let label: string;
</script>

<div>
  <div class="flex items-center justify-between">
    {#if label}
      <label for={$$props['id']} class="block text-sm text-label-default">
        {label}
      </label>
    {/if}

    <slot name="topRightText" />
  </div>

  <div class="relative">
    {#if $$slots.leftIcon}
      <div
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 opacity-60"
      >
        <slot name="leftIcon" />
      </div>
    {/if}
    <input
      {...$$props}
      class:pl-10={$$slots.leftIcon}
      class:pr-10={$$slots.rightIcon}
      class:border-input-error={$$slots.error}
      class:focus:border-input-error={$$slots.error}
      class:focus:ring-input-focus-error={$$slots.error}
      class="mt-2 block w-full rounded-lg border border-input-default bg-input-default px-4 py-2 text-input-default focus:border-input-focus-default focus:outline-none focus:ring focus:ring-input-focus-default focus:ring-opacity-40"
    />
    {#if $$slots.rightIcon}
      <div
        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 opacity-60"
      >
        <slot name="rightIcon" />
      </div>
    {/if}
  </div>

  {#if $$slots.error}
    <div class="mt-1 text-xs text-input-error">
      <slot name="error" />
    </div>
  {/if}
</div>
