<script lang="ts">
  import Icon from '@iconify/svelte';

  // Let's see what props svelte-sonner actually passes
  let props = $props();

  $effect(() => {
    console.log('All props received by TestToast:', props);
  });
</script>

<div class="p-4 bg-gray-100 border rounded">
  <p>Test Toast - Props: {JSON.stringify(props)}</p>
</div>
