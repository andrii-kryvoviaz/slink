import type { ChartNormalizer, ChartOptions } from '@slink/components/UI/Chart';

export class AreaChart implements ChartNormalizer {
  normalize(options: ChartOptions): ChartOptions {
    return {
      ...options,
      fill: {
        type: 'gradient',
        gradient: {
          opacityFrom: 0.55,
          opacityTo: 0,
        },
      },
      stroke: {
        curve: 'smooth',
        width: 4,
      },
    };
  }
}
