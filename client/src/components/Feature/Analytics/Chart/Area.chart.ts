import {
  type ChartNormalizer,
  type ChartOptions,
} from '@slink/components/Feature/Analytics';

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
