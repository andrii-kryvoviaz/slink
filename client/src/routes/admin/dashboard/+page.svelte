<script lang="ts">
  import { fade } from 'svelte/transition';

  import {
    ImageAnalytics,
    LatestUsers,
    UserAnalytics,
  } from '@slink/components/Analytics';
</script>

<section in:fade={{ duration: 300 }} class="relative h-full flex-grow py-4">
  <div class="grid h-full grid-cols-2 content-start gap-6 px-6">
    <div class="col-span-2 h-96 md:col-span-1 xl:h-[40rem]">
      <ImageAnalytics />
    </div>
    <div class="col-span-2 h-96 md:col-span-1 xl:h-[40rem]">
      <UserAnalytics />
    </div>

    <div class="col-span-2">
      <LatestUsers />
    </div>
  </div>
</section>
