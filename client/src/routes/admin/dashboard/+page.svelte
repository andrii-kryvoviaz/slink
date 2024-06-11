<script lang="ts">
  import { fade } from 'svelte/transition';

  import { Chart, type ChartOptions } from '@slink/components/Analytics';
  import { Card, Heading } from '@slink/components/Layout';

  let options: ChartOptions = {
    series: [
      {
        name: 'New users',
        data: [10, 23, 15, 40, 60, 5],
      },
      {
        name: 'Returning users',
        data: [5, 10, 20, 15, 30, 10],
      },
      {
        name: 'Active users',
        data: [10, 15, 20, 25, 30, 35],
      },
      {
        name: 'Total users',
        data: [10, 20, 30, 40, 50, 60],
      },
    ],
    xaxis: {
      categories: [
        '01 Feb',
        '02 Feb',
        '03 Feb',
        '04 Feb',
        '05 Feb',
        '06 Feb',
        '07 Feb',
      ],
    },
  };
</script>

<section
  in:fade={{ duration: 300 }}
  class="relative flex h-full flex-grow flex-col py-4"
>
  <div class="flex h-full flex-col">
    <div class="px-6">
      <Heading alignment="left" size="sm" fontWeight="normal">
        <span>Dashboard</span>
      </Heading>
    </div>

    <div class="flex flex-col gap-6 p-6">
      <Card>
        <Chart {options} />
      </Card>
    </div>
  </div>
</section>
