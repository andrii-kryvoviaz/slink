<script lang="ts">
  import { onMount } from 'svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { UserAnalyticsData } from '@slink/api/Response';

  import { Chart, type ChartOptions } from '@slink/components/Analytics';
  import { Card } from '@slink/components/Layout';

  const {
    run,
    data: response,
    isLoading,
    status,
  } = ReactiveState<UserAnalyticsData>(
    () => {
      return ApiClient.analytics.getUserAnalytics();
    },
    { debounce: 300 }
  );

  let options: ChartOptions = {};

  $: if ($response) {
    const labels = Object.keys($response).map((key) => {
      return key.capitalizeFirstLetter();
    });

    const series = Object.values($response).filter((value) => {
      return value > 0;
    });

    options = {
      labelFormatter: function (value) {
        return `${value} Users`;
      },
      chart: {
        type: 'radialBar',
        height: 500,
      },
      series,
      labels,
      colors: ['#4B8EDD', '#7029FF', '#4B5563'],
    };
  }

  onMount(run);
</script>

<Card>
  {#if $isLoading}
    <p>Loading...</p>
  {:else}
    <p>User Analytics</p>
  {/if}
  {#if $status === 'finished'}
    <Chart {options} />
  {/if}
</Card>
