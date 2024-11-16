<script lang="ts">
  import Icon from '@iconify/svelte';

  export let removeToast: () => void = () => {};
</script>

<div class="flex items-center gap-2">
  <slot name="icon">
    <Icon
      icon="clarity:info-standard-line"
      class="relative top-[-2px] mr-1 inline-block text-2xl text-blue-500"
    />
  </slot>
  <div class="w-full text-left text-sm font-normal">
    <slot>Alert Message</slot>
  </div>
  <button
    type="button"
    class="m-0.5 ml-auto cursor-pointer whitespace-normal rounded-lg p-1.5 hover:bg-gray-100 focus:outline-none focus:ring-gray-400 dark:hover:bg-gray-600"
    aria-label="Close"
    on:click={removeToast}
    ><span class="sr-only">Close</span>
    <Icon icon="ic:round-close" /></button
  >
</div>
