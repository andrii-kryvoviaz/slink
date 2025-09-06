<script lang="ts">
  import {
    ImageAnalytics,
    LatestUsers,
    UserAnalytics,
  } from '@slink/feature/Analytics';

  import { fade } from 'svelte/transition';
</script>

<svelte:head>
  <title>Dashboard | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }} class="relative h-full grow py-4">
  <div class="grid h-full grid-cols-1 md:grid-cols-5 content-start gap-6 px-6">
    <div class="col-span-1 md:col-span-3 h-96 xl:h-[40rem]">
      <ImageAnalytics />
    </div>
    <div class="col-span-1 md:col-span-2 h-96 xl:h-[40rem]">
      <UserAnalytics />
    </div>

    <div class="col-span-1 md:col-span-5">
      <LatestUsers />
    </div>
  </div>
</section>
