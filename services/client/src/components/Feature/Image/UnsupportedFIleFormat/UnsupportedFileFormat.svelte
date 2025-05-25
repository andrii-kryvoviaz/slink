<script lang="ts">
  import Icon from '@iconify/svelte';

  let { close } = $props();
</script>

<div class="flex items-center gap-2">
  <Icon
    icon="clarity:info-standard-line"
    class="relative top-[-2px] mr-1 inline-block text-2xl text-red-500"
  />
  <div class="w-full text-left text-sm font-normal">
    <p>Unsupported file format.</p>
    <a
      href="/help/faq#supported-image-formats"
      class="text-indigo-500 hover:text-indigo-700 mt-1 block"
      >See supported formats</a
    >
  </div>
  <button
    type="button"
    class="m-0.5 ml-auto cursor-pointer whitespace-normal rounded-lg p-1.5 hover:bg-gray-100 focus:outline-hidden focus:ring-gray-400 dark:hover:bg-gray-600"
    aria-label="Close"
    onclick={close}
    ><span class="sr-only">Close</span>
    <Icon icon="ic:round-close" /></button
  >
</div>
