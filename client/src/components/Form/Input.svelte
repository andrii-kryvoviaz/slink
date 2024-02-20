<script lang="ts">
  export let label: string;
</script>

<div class="flex items-center justify-between">
  {#if label}
    <label for={$$props['id']} class="block text-sm text-label-default">
      {label}
    </label>
  {/if}

  <slot name="postfix" />
</div>

<input
  {...$$props}
  class="mt-2 block w-full rounded-lg border border-input-default bg-input-default px-4 py-2 text-input-default focus:border-input-focus-default focus:outline-none focus:ring focus:ring-input-focus focus:ring-opacity-40"
/>
