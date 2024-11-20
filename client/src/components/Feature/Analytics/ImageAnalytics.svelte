<script lang="ts">
  import { onMount } from 'svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { ImageAnalyticsResponse } from '@slink/api/Response';

  import { RefreshButton } from '@slink/components/UI/Action';
  import { Card } from '@slink/components/UI/Card';
  import { Chart, type ChartOptions } from '@slink/components/UI/Chart';
  import {
    Dropdown,
    DropdownItem,
    type DropdownItemData,
  } from '@slink/components/UI/Form';

  const {
    run,
    data: response,
    isLoading,
  } = ReactiveState<ImageAnalyticsResponse>(
    ({ dateInterval }: { dateInterval?: string } = {}) => {
      return ApiClient.analytics.getImageAnalytics({ dateInterval });
    },
    { minExecutionTime: 1000 }
  );

  let options: ChartOptions = {
    series: [],
  };
  let interval: string = 'current_year';

  const handleIntervalChange = ({ detail }: CustomEvent<DropdownItemData>) => {
    interval = detail.key;
    handleFetch();
  };

  const handleFetch = () => {
    run({ dateInterval: interval });
  };

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
        tickAmount: 10,
      },
    };
  }

  let availableIntervals: Record<string, string> | null = null;

  $: if ($response?.availableIntervals) {
    availableIntervals = $response.availableIntervals;
  }

  onMount(handleFetch);
</script>

<Card class="h-full">
  <div class="flex items-center justify-between">
    <p class="text-lg font-light tracking-wider">Uploads</p>
    <div class="flex items-center gap-2">
      <RefreshButton size="sm" loading={$isLoading} on:click={handleFetch} />
      {#if availableIntervals}
        <Dropdown
          variant="invisible"
          position="bottom-right"
          selected={interval}
          on:change={handleIntervalChange}
        >
          {#each Object.keys(availableIntervals) as interval}
            <DropdownItem key={interval}>
              {availableIntervals[interval]}
            </DropdownItem>
          {/each}
        </Dropdown>
      {/if}
    </div>
  </div>

  <Chart {options} />
</Card>
