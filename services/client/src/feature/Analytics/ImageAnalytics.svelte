<script lang="ts">
  import { ApiClient } from '@slink/api';
  import { RefreshButton } from '@slink/feature/Action';
  import { Card } from '@slink/feature/Layout';
  import { Chart, type ChartOptions } from '@slink/feature/Layout';
  import { Select } from '@slink/ui/components';
  import { onMount } from 'svelte';

  import { t } from '$lib/i18n';

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

  const getIntervalLabel = (value: string, fallback: string) => {
    const key = `pages.admin.dashboard.intervals.${value}`;
    const translated = $t(key);
    return translated === key ? fallback : translated;
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
            name: $t('pages.admin.dashboard.cards.uploads'),
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
    <p class="text-lg font-semibold text-gray-900 dark:text-white">
      {$t('pages.admin.dashboard.cards.uploads')}
    </p>
    <div class="flex items-center gap-2">
      <RefreshButton size="sm" loading={$isLoading} onclick={handleFetch} />
      {#if availableIntervals}
        <Select
          type="single"
          class="min-w-fit"
          size="sm"
          items={Object.keys(availableIntervals).map((value) => ({
            value,
            label: getIntervalLabel(
              value,
              availableIntervals?.[value] ?? 'N/A',
            ),
          }))}
          bind:value={interval}
        />
      {/if}
    </div>
  </div>

  <Chart {options} />
</Card>
