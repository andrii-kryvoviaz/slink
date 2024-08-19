<script lang="ts">
  import { onMount } from 'svelte';

  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { ImageAnalyticsResponse } from '@slink/api/Response';

  import { Chart, type ChartOptions } from '@slink/components/Analytics';
  import {
    Button,
    Dropdown,
    DropdownItem,
    type DropdownItemData,
    RefreshButton,
  } from '@slink/components/Common';
  import { Card } from '@slink/components/Layout';

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
  let interval: string = 'last_7_days';

  const handleIntervalChange = ({ detail }: CustomEvent<DropdownItemData>) => {
    interval = detail.key;
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
      },
    };
  }

  let availableIntervals: Record<string, string> | null = null;

  $: if ($response?.availableIntervals) {
    availableIntervals = $response.availableIntervals;
  }

  onMount(run);
</script>

<Card>
  <div class="flex items-center justify-between">
    <p class="text-lg font-light tracking-wider">Image Analytics</p>
    <RefreshButton size="sm" loading={$isLoading} on:click={run} />
  </div>

  <Chart {options} />

  {#if availableIntervals}
    <div
      class="flex flex-row-reverse items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700"
    >
      <Dropdown
        variant="invisible"
        position="top-right"
        selected={interval}
        on:change={handleIntervalChange}
      >
        {#each Object.keys(availableIntervals) as interval}
          <DropdownItem key={interval}>
            {availableIntervals[interval]}
          </DropdownItem>
        {/each}
      </Dropdown>
    </div>
  {/if}
</Card>
