<script lang="ts">
  import { settings } from '@slink/lib/settings';
  import type { ApexOptions } from 'apexcharts';

  import { deepMerge } from '@slink/utils/object/deepMerge';

  import type { ChartOptions } from '@slink/components/Analytics';

  export let options: ChartOptions;

  let defaultOptions: ChartOptions = {
    chart: {
      width: '100%',
      type: 'area',
      toolbar: {
        show: false,
      },
      zoom: {
        enabled: false,
      },
      background: 'transparent',
    },
    tooltip: {
      x: {
        show: false,
      },
    },
    fill: {
      type: 'gradient',
      gradient: {
        opacityFrom: 0.55,
        opacityTo: 0,
      },
    },
    dataLabels: {
      enabled: false,
    },
    stroke: {
      width: 4,
    },
    grid: {
      show: false,
      strokeDashArray: 4,
      padding: {
        left: 2,
        right: 2,
        top: 0,
      },
    },
    colors: ['#1A56DB', '#7029FF', '#5AC8FF', '#62CC11', '#FFC107', '#FF5722'],
    xaxis: {
      labels: {
        trim: true,
        rotateAlways: true,
        hideOverlappingLabels: true,
        style: {
          cssClass: 'text-red-200',
        },
      },
      axisBorder: {
        show: false,
      },
      axisTicks: {
        show: false,
      },
      tooltip: {
        enabled: false,
      },
    },
    yaxis: {
      show: false,
    },
  };

  function initChart(node: HTMLElement, options: ApexOptions) {
    let chart: ApexCharts;

    async function asyncInitChart() {
      const ApexCharts = (await import('apexcharts')).default;
      chart = new ApexCharts(node, options);
      await chart.render();
    }

    asyncInitChart();

    return {
      update(options: ApexOptions) {
        chart && chart.updateOptions(options);
      },
      destroy() {
        chart && chart.destroy();
      },
    };
  }

  const classes = `${$$props.class} text-black`;

  const currentTheme = settings.get('theme', 'light');
  const { isDark, isLight } = currentTheme;

  $: options = deepMerge<ChartOptions>(defaultOptions, options);
  $: if ($isDark) {
    options.theme = { mode: 'dark' };
  }
  $: if ($isLight) {
    options.theme = { mode: 'light' };
  }
</script>

<div use:initChart={options} class={classes} />
