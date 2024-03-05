<div class="flex flex-grow items-center justify-center gap-4">
  <div
    class="h-5 w-5 animate-spin rounded-full border-b-2 border-t-2 border-indigo-500"
  />
  {#if $$slots.default}
    <slot />
  {/if}
</div>
