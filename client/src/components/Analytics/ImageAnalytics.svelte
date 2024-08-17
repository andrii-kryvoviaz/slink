<script lang="ts">
  import { onMount } from 'svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { ImageAnalyticsResponse } from '@slink/api/Response';

  import { Chart, type ChartOptions } from '@slink/components/Analytics';
  import {
    Dropdown,
    DropdownItem,
    type DropdownItemData,
  } from '@slink/components/Common';
  import { Card } from '@slink/components/Layout';

  const {
    run,
    data: response,
    isLoading,
    status,
  } = ReactiveState<ImageAnalyticsResponse>(
    ({ dateInterval }: { dateInterval?: string } = {}) => {
      return ApiClient.analytics.getImageAnalytics({ dateInterval });
    },
    { debounce: 300 }
  );

  let options: ChartOptions = {};
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

  $: availableIntervals = $response?.availableIntervals || {};

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
