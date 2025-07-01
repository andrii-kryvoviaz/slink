<script lang="ts">
  import type { ApexOptions } from 'apexcharts';
  import { twMerge } from 'tailwind-merge';

  import { settings } from '@slink/lib/settings';

  import { deepMerge } from '@slink/utils/object/deepMerge';

  import type {
    ChartNormalizer,
    ChartOptions,
  } from '@slink/components/UI/Chart';
  import { AreaChart } from '@slink/components/UI/Chart/Area.chart';
  import { RadialBarChart } from '@slink/components/UI/Chart/RadialBar.chart';

  interface Props {
    class?: string;
    options: ChartOptions;
  }

  let { options, ...props }: Props = $props();

  let defaultOptions: ChartOptions = {
    chart: {
      height: '100%',
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
    dataLabels: {
      enabled: false,
    },
    stroke: {
      width: 0,
    },
    grid: {
      show: false,
    },
    legend: {
      show: true,
      position: 'bottom',
      horizontalAlign: 'center',
      fontSize: '14px',
      markers: {
        size: 7,
        strokeWidth: 0,
        offsetX: -5,
      },
      itemMargin: {
        horizontal: 10,
        vertical: 10,
      },
    },
    colors: ['#1A56DB', '#7029FF', '#5AC8FF', '#62CC11', '#FFC107', '#FF5722'],
    xaxis: {
      labels: {
        trim: true,
        rotateAlways: true,
        hideOverlappingLabels: true,
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
    series: [],
  };

  const supportedCharts: { [key: string]: new () => ChartNormalizer } = {
    area: AreaChart,
    radialBar: RadialBarChart,
  };

  const getChartNormalizer = (type: string): ChartNormalizer | null => {
    const ChartNormalizer = supportedCharts[type];

    if (!ChartNormalizer) {
      return null;
    }

    return new ChartNormalizer();
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

  const classes = twMerge('w-full', props.class ?? '');

  const currentTheme = settings.get('theme', 'light');
  const { isDark, isLight } = currentTheme;

  const handleOptionsChange = (
    options: ChartOptions,
    theme: 'dark' | 'light',
  ) => {
    let chartOptions = deepMerge<ChartOptions>(defaultOptions, options);

    if (chartOptions.chart?.type) {
      const chartType = chartOptions.chart.type;
      const chartNormalizer = getChartNormalizer(chartType);

      if (chartNormalizer) {
        chartOptions = chartNormalizer.normalize(chartOptions);
      }
    }

    chartOptions.theme = { mode: theme };

    return chartOptions;
  };

  let chartOptions = $derived(
    handleOptionsChange(options, $isDark ? 'dark' : 'light'),
  );
</script>

<div use:initChart={chartOptions} class={classes}></div>
