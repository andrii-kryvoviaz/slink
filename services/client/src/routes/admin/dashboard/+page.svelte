<script lang="ts">
  import {
    ImageAnalytics,
    LatestUsers,
    UserAnalytics,
  } from '@slink/feature/Analytics';
  import { Subtitle, Title } from '@slink/feature/Text';

  import { fade } from 'svelte/transition';
</script>

<svelte:head>
  <title>Dashboard | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }} class="relative h-full grow">
  <div class="px-6 py-6">
    <div class="mb-8">
      <Title>Dashboard</Title>
      <Subtitle>Overview of key metrics and activity</Subtitle>
    </div>

    <div class="grid h-full grid-cols-1 md:grid-cols-5 content-start gap-4">
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
  </div>
</section>
