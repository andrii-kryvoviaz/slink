<script lang="ts">
  import { fade } from 'svelte/transition';

  import { ImageAnalytics, UserAnalytics } from '@slink/components/Analytics';
</script>

<section
  in:fade={{ duration: 300 }}
  class="relative flex h-full flex-grow flex-col py-4"
>
  <div class="flex h-full flex-col gap-6 px-6">
    <div class="flex flex-col gap-6 md:flex-row">
      <ImageAnalytics />
      <UserAnalytics />
    </div>
  </div>
</section>
