<script lang="ts">
  import type { ChartNormalizer, ChartOptions } from '@slink/feature/Layout';
  import { AreaChart, RadialBarChart } from '@slink/feature/Layout';
  import type { ApexOptions } from 'apexcharts';
  import type ApexCharts from 'apexcharts';
  import { twMerge } from 'tailwind-merge';

  import { browser } from '$app/environment';
  import { page } from '$app/state';
  import { Mode } from '$lib/settings';
  import { deepMerge } from '$lib/utils/object/deepMerge';

  interface Props {
    class?: string;
    options: ChartOptions;
  }

  let { options, ...props }: Props = $props();

  const { settings } = page.data;

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

  const SERIES_UTILITIES = [
    'text-chart-1',
    'text-chart-2',
    'text-chart-3',
    'text-chart-4',
    'text-chart-5',
  ];

  const toHex = (context: CanvasRenderingContext2D, color: string): string => {
    context.fillStyle = color;
    context.fillRect(0, 0, 1, 1);

    const [red, green, blue] = context.getImageData(0, 0, 1, 1).data;
    return `#${[red, green, blue].map((channel) => channel.toString(16).padStart(2, '0')).join('')}`;
  };

  const resolveSeriesColors = (
    node: HTMLElement,
    utilities: string[],
  ): string[] => {
    const probe = document.createElement('span');
    probe.style.display = 'none';
    node.appendChild(probe);

    const context = document.createElement('canvas').getContext('2d');
    const colors = utilities.map((utility) => {
      probe.className = utility;
      const color = getComputedStyle(probe).color;
      return context ? toHex(context, color) : color;
    });

    probe.remove();
    return colors;
  };

  function initChart(node: HTMLElement, options: ChartOptions) {
    let chart: ApexCharts;

    const withSeriesColors = (options: ChartOptions): ApexOptions => ({
      ...options,
      colors: resolveSeriesColors(
        node,
        options.seriesClasses ?? SERIES_UTILITIES,
      ),
    });

    async function asyncInitChart() {
      const ApexCharts = (await import('apexcharts')).default;
      chart = new ApexCharts(node, withSeriesColors(options));
      await chart.render();
    }

    asyncInitChart();

    return {
      update(options: ChartOptions) {
        chart && chart.updateOptions(withSeriesColors(options));
      },
      destroy() {
        chart && chart.destroy();
      },
    };
  }

  const classes = twMerge('w-full', props.class ?? '');

  const handleOptionsChange = (
    options: ChartOptions,
    mode: 'dark' | 'light',
  ) => {
    let chartOptions = deepMerge(
      defaultOptions as any,
      options as any,
    ) as ChartOptions;

    if (chartOptions.chart?.type) {
      const chartType = chartOptions.chart.type;
      const chartNormalizer = getChartNormalizer(chartType);

      if (chartNormalizer) {
        chartOptions = chartNormalizer.normalize(chartOptions);
      }
    }

    chartOptions.theme = { mode };

    return chartOptions;
  };

  const isDarkMode = (): boolean => {
    if (browser && settings.mode.current === Mode.SYSTEM) {
      return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    return settings.mode.isDark;
  };

  let chartOptions = $derived(
    handleOptionsChange(options, isDarkMode() ? 'dark' : 'light'),
  );
</script>

<div use:initChart={chartOptions} class={classes}></div>
