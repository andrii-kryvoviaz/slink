import type { ApexOptions } from 'apexcharts';

export type LabelFormatter = (value: number | string) => string;

export interface ChartOptions extends ApexOptions {
  labelFormatter?: LabelFormatter;
  totalLabel?: string;
}

export interface ChartNormalizer {
  normalize: (options: ChartOptions) => ChartOptions;
}
