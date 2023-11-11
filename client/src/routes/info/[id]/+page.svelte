<script lang="ts">
  import Button from '@slink/components/Shared/Action/Button.svelte';
  import ImagePlaceholder from '../../../components/Image/Preview/ImagePlaceholder.svelte';
  import type { PageData } from './$types';
  import { fly } from 'svelte/transition';
  import CopyContainer from '@slink/components/Shared/Action/CopyContainer.svelte';

  export let data: PageData;
</script>

<div
  in:fly={{ y: 100, duration: 500, delay: 100 }}
  class="flex justify-center p-8"
>
  <div
    class="container flex max-w-[1280px] flex-col items-center gap-6 lg:flex-row lg:items-start"
  >
    <div class="w-full">
      <div class="flex justify-center">
        <ImagePlaceholder
          src={data.url}
          height={32}
          aspectRatio={data.height / data.width}
          metadata={data}
        />
      </div>

      <Button
        class="mr-2 mt-4"
        variant="secondary"
        size="md"
        rounded="full"
        target="_self"
        href="/">Upload More</Button
      >
    </div>

    <div class="w-full px-2">
      <p
        class="mb-4 mt-8 border-l-4 border-slate-800 pl-2 text-xl font-bold text-slate-900 dark:border-slate-400 dark:text-slate-100"
      >
        {data.description || 'No description provided yet.'}
      </p>
      <div>
        <CopyContainer value={data.url} />
      </div>
    </div>
  </div>
</div>
