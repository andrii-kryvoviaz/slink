import type { ApexOptions } from 'apexcharts';

export type LabelFormatter = (value: number | string) => string;

export interface ChartOptions extends ApexOptions {
  labelFormatter?: LabelFormatter;
}

export interface ChartNormalizer {
  normalize: (options: ChartOptions) => ChartOptions;
}
