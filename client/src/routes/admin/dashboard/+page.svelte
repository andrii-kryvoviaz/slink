<script lang="ts">
  import { fade } from 'svelte/transition';

  import { ImageAnalytics, UserAnalytics } from '@slink/components/Analytics';
  import { Heading } from '@slink/components/Layout';
</script>

<section
  in:fade={{ duration: 300 }}
  class="relative flex h-full flex-grow flex-col py-4"
>
  <div class="flex h-full flex-col">
    <div class="px-6">
      <Heading alignment="left" size="sm" fontWeight="normal">
        <span>Dashboard</span>
      </Heading>
    </div>

    <div class="flex flex-col gap-6 p-6">
      <div class="flex flex-col gap-6 md:flex-row">
        <ImageAnalytics />
        <UserAnalytics />
      </div>
    </div>
  </div>
</section>
