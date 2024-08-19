<script lang="ts">
  import { settings } from '@slink/lib/settings';
  import type { ApexOptions } from 'apexcharts';

  import { deepMerge } from '@slink/utils/object/deepMerge';

  import type {
    ChartNormalizer,
    ChartOptions,
  } from '@slink/components/Analytics';
  import { AreaChart } from '@slink/components/Analytics/Chart/Area.chart';
  import { RadialBarChart } from '@slink/components/Analytics/Chart/RadialBar.chart';

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
    dataLabels: {
      enabled: false,
    },
    stroke: {
      width: 0,
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
    legend: {
      show: true,
      position: 'bottom',
      horizontalAlign: 'center',
      fontSize: '14px',
      markers: {
        width: 12,
        height: 12,
        radius: 12,
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
    noData: {
      text: 'No data available',
      align: 'center',
      verticalAlign: 'middle',
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

  $: if (options.chart?.type) {
    const chartType = options.chart.type;
    const chartNormalizer = getChartNormalizer(chartType);

    if (chartNormalizer) {
      options = chartNormalizer.normalize(options);
    }
  }
</script>

<div use:initChart={options} class={classes} />
