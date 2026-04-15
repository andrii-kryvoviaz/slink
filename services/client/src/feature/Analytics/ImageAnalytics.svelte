<script lang="ts">
  import { ApiClient } from '@slink/api';
  import { RefreshButton } from '@slink/feature/Action';
  import { Card } from '@slink/feature/Layout';
  import { Chart, type ChartOptions } from '@slink/feature/Layout';
  import { Select } from '@slink/ui/components';
  import { onMount } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { ImageAnalyticsResponse } from '@slink/api/Response';

  const {
    run,
    data: response,
    isLoading,
  } = ReactiveState<ImageAnalyticsResponse>(
    ({ dateInterval }: { dateInterval?: string }) => {
      return ApiClient.analytics.getImageAnalytics({ dateInterval });
    },
    { minExecutionTime: 1000 },
  );

  let options: ChartOptions = $state({
    chart: {
      type: 'area',
      height: '98%',
    },
    series: [],
  });

  let interval: string = $state('current_year');

  $effect(() => {
    if (interval) {
      handleFetch();
    }
  });

  const handleFetch = () => {
    run({ dateInterval: interval });
  };

  let isEmpty: boolean = $state(false);
  let availableIntervals: Record<string, string> | null = $state(null);

  onMount(() => {
    handleFetch();

    return response.subscribe((item) => {
      if (!item) {
        return;
      }

      availableIntervals = item.availableIntervals;
      isEmpty = item.data.length === 0;

      options = {
        series: [
          {
            name: 'Uploads',
            data: item.data.map((item) => item.count),
          },
        ],
        xaxis: {
          categories: item.data.map((item) => item.date),
          tickAmount: 10,
        },
      };
    });
  });
</script>

<Card class="h-full" variant="enhanced" rounded="xl" shadow="lg">
  <div class="flex items-center justify-between">
    <p class="text-lg font-semibold text-gray-900 dark:text-white">Uploads</p>
    <div class="flex items-center gap-2">
      <RefreshButton size="sm" loading={$isLoading} onclick={handleFetch} />
      {#if availableIntervals}
        <Select
          type="single"
          class="min-w-fit"
          size="sm"
          items={Object.keys(availableIntervals).map((value) => ({
            value,
            label: availableIntervals?.[value] ?? 'N/A',
          }))}
          bind:value={interval}
        />
      {/if}
    </div>
  </div>

  {#if isEmpty && !$isLoading}
    <div
      class="flex flex-col items-center justify-center py-12 text-center"
      in:fade={{ duration: 200 }}
    >
      <div
        class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4"
      >
        <Icon
          icon="heroicons:chart-bar"
          class="w-7 h-7 text-gray-400 dark:text-gray-500"
        />
      </div>
      <h3 class="text-base font-medium text-gray-900 dark:text-white mb-1">
        No uploads yet
      </h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs">
        Upload data will appear here once images are added
      </p>
    </div>
  {:else}
    <Chart {options} />
  {/if}
</Card>
