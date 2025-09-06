import type { ChartNormalizer, ChartOptions } from '@slink/legacy/Feature';

export class RadialBarChart implements ChartNormalizer {
  normalize(options: ChartOptions): ChartOptions {
    const { series, labelFormatter } = options;

    if (!series || !series.length) {
      return options;
    }

    const defaultFormatter = (value: number | string) => `${value}`;
    const formatter = labelFormatter || defaultFormatter;
    const flatSeries = series.flat() as number[];

    const total = flatSeries.reduce((acc: number, cur: number) => acc + cur, 0);

    const absoluteSeries = flatSeries.map((value) => {
      return (value / total) * 100;
    });

    return {
      ...options,

      series: absoluteSeries,
      stroke: {
        lineCap: 'round',
      },
      tooltip: {
        enabled: true,
        y: {
          formatter: (value: number) => {
            const absoluteValue = this.calculateRealValue(value, total);
            return formatter(absoluteValue);
          },
        },
      },
      plotOptions: {
        radialBar: {
          hollow: {
            margin: 0,
            size: '60%',
          },
          dataLabels: {
            name: {
              show: true,
            },
            value: {
              show: true,
              formatter: (value: number) => {
                const absoluteValue = this.calculateRealValue(value, total);
                return formatter(absoluteValue);
              },
            },
            total: {
              show: true,
              label: 'Total',
              formatter: function () {
                return formatter(total);
              },
            },
          },
          track: {
            show: false,
          },
        },
      },
    };
  }

  private calculateRealValue(value: number, total: number) {
    return Math.round((total * value) / 100);
  }
}
