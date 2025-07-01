<script lang="ts">
  import { onMount } from 'svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { UserAnalyticsData } from '@slink/api/Response';

  import { RefreshButton } from '@slink/components/UI/Action';
  import { Card } from '@slink/components/UI/Card';
  import { Chart, type ChartOptions } from '@slink/components/UI/Chart';

  const {
    run,
    data: response,
    isLoading,
  } = ReactiveState<UserAnalyticsData>(
    () => {
      return ApiClient.analytics.getUserAnalytics();
    },
    { minExecutionTime: 1000 },
  );

  let options: ChartOptions = $state({
    labelFormatter: function (value) {
      return `${value} Users`;
    },
    chart: {
      type: 'radialBar',
      height: '95%',
    },
    colors: ['#4B8EDD', '#7029FF', '#4B5563'],
    series: [0],
    labels: ['No Data'],
  });

  onMount(() => {
    run();

    return response.subscribe((item) => {
      if (!item) {
        return;
      }

      const labels = Object.keys(item).map((key) => {
        return key.capitalizeFirstLetter();
      });

      const series = Object.values(item).filter((value) => {
        return value > 0;
      });

      options = {
        ...options,
        series,
        labels,
      };
    });
  });
</script>

<Card class="h-full" variant="enhanced" rounded="xl" shadow="lg">
  <div class="flex items-center justify-between">
    <p class="text-lg font-semibold text-slate-900 dark:text-white">Users</p>
    <RefreshButton size="sm" loading={$isLoading} onclick={run} />
  </div>

  <Chart {options} />
</Card>
