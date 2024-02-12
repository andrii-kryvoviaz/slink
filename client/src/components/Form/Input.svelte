<script lang='ts'>
  export let label: string;
</script>

<div class='flex items-center justify-between'>
  {#if label}
    <label for={$$props['id']} class='block text-sm text-label-default'>
      {label}
    </label>
  {/if}

  <slot name="postfix" />
</div>

<input {...$$props}
       class='block w-full px-4 py-2 mt-2 text-input-default bg-input-default border rounded-lg border-input-default focus:border-input-focus-default focus:ring-input-focus focus:ring focus:outline-none focus:ring-opacity-40' />