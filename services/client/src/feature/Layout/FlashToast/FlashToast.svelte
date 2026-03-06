<script lang="ts">
  import { page } from '$app/state';

  import type { FlashMessage } from '@slink/lib/flash/flash';

  import { toast } from '@slink/utils/ui/toast-sonner.svelte';

  let messages = $derived((page.data.flash ?? []) as FlashMessage[]);

  $effect(() => {
    for (const flash of messages) {
      toast[flash.type](flash.message);
    }
  });
</script>
