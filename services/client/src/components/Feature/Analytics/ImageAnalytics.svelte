<script lang="ts">
  import { onMount } from 'svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { ImageAnalyticsResponse } from '@slink/api/Response';

  import { RefreshButton } from '@slink/components/UI/Action';
  import { Card } from '@slink/components/UI/Card';
  import { Chart, type ChartOptions } from '@slink/components/UI/Chart';
  import { Select } from '@slink/components/UI/Form';

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

  const handleIntervalChange = (item: string) => {
    interval = item;
    handleFetch();
  };

  const handleFetch = () => {
    run({ dateInterval: interval });
  };

  let availableIntervals: Record<string, string> | null = $state(null);

  onMount(() => {
    handleFetch();

    return response.subscribe((item) => {
      if (!item) {
        return;
      }

      availableIntervals = item.availableIntervals;

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
    <p class="text-lg font-semibold text-slate-900 dark:text-white">Uploads</p>
    <div class="flex items-center gap-2">
      <RefreshButton size="sm" loading={$isLoading} onclick={handleFetch} />
      {#if availableIntervals}
        <Select
          type="single"
          class="min-w-fit"
          contentClass="w-48 text-sm"
          variant="invisible"
          rounded="full"
          size="sm"
          items={Object.keys(availableIntervals).map((value) => ({
            value,
            label: availableIntervals?.[value] ?? 'N/A',
          }))}
          value={interval.toString()}
          onValueChange={handleIntervalChange}
        />
      {/if}
    </div>
  </div>

  <Chart {options} />
</Card>
