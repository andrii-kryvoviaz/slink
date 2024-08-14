<script lang="ts">
  import { onMount } from 'svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { ImageAnalyticsResponse } from '@slink/api/Response';

  import { Chart, type ChartOptions } from '@slink/components/Analytics';
  import { Card } from '@slink/components/Layout';

  const {
    run,
    data: response,
    isLoading,
    status,
  } = ReactiveState<ImageAnalyticsResponse>(
    () => {
      return ApiClient.analytics.getImageAnalytics();
    },
    { debounce: 300 }
  );

  let options: ChartOptions = {};

  $: if ($response) {
    options = {
      series: [
        {
          name: 'Uploads',
          data: $response.data.map((item) => item.count),
        },
      ],
      xaxis: {
        categories: $response.data.map((item) => item.date),
      },
    };
  }

  onMount(run);
</script>

<Card>
  {#if $isLoading}
    <p>Loading...</p>
  {:else}
    <p>Image Analytics</p>
  {/if}
  {#if $status === 'finished'}
    <Chart {options} />
  {/if}
</Card>
