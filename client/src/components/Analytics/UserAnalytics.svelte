<script lang="ts">
  import { onMount } from 'svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { UserAnalyticsData } from '@slink/api/Response';

  import { Chart, type ChartOptions } from '@slink/components/Analytics';
  import { RefreshButton } from '@slink/components/Common';
  import { Card } from '@slink/components/Layout';

  const {
    run,
    data: response,
    isLoading,
  } = ReactiveState<UserAnalyticsData>(
    () => {
      return ApiClient.analytics.getUserAnalytics();
    },
    { minExecutionTime: 1000 }
  );

  let options: ChartOptions = {
    labelFormatter: function (value) {
      return `${value} Users`;
    },
    chart: {
      type: 'radialBar',
    },
    colors: ['#4B8EDD', '#7029FF', '#4B5563'],
  };

  $: if ($response) {
    const labels = Object.keys($response).map((key) => {
      return key.capitalizeFirstLetter();
    });

    const series = Object.values($response).filter((value) => {
      return value > 0;
    });

    options = {
      ...options,
      series,
      labels,
    };
  } else if (!options.series?.length) {
    options = {
      ...options,
      series: [0],
      labels: ['No Data'],
    };
  }

  onMount(run);
</script>

<Card class="h-full">
  <div class="flex items-center justify-between">
    <p class="text-lg font-light tracking-wider">Users</p>
    <RefreshButton size="sm" loading={$isLoading} on:click={run} />
  </div>

  <Chart {options} />
</Card>
