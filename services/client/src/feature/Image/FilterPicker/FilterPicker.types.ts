export type ImageFilter =
  | 'none'
  | 'dramatic'
  | 'noir'
  | 'sepia'
  | 'warm'
  | 'cool'
  | 'vivid'
  | 'fade';

export type FilterOption = {
  value: ImageFilter;
  label: string;
  cssFilter: string;
};

export const FILTER_MAP: Record<ImageFilter, FilterOption> = {
  none: { value: 'none', label: 'None', cssFilter: '' },
  dramatic: {
    value: 'dramatic',
    label: 'Dramatic',
    cssFilter: 'saturate(70%) contrast(140%) brightness(90%)',
  },
  noir: {
    value: 'noir',
    label: 'Noir',
    cssFilter: 'grayscale(100%) contrast(130%) brightness(90%)',
  },
  sepia: { value: 'sepia', label: 'Sepia', cssFilter: 'sepia(80%)' },
  warm: {
    value: 'warm',
    label: 'Warm',
    cssFilter: 'sepia(30%) saturate(120%)',
  },
  cool: {
    value: 'cool',
    label: 'Cool',
    cssFilter: 'saturate(80%) hue-rotate(15deg)',
  },
  vivid: {
    value: 'vivid',
    label: 'Vivid',
    cssFilter: 'saturate(150%) contrast(105%)',
  },
  fade: {
    value: 'fade',
    label: 'Fade',
    cssFilter: 'contrast(80%) brightness(110%)',
  },
};

export const FILTER_OPTIONS: FilterOption[] = Object.values(FILTER_MAP);

export function getCssFilter(filter: ImageFilter): string {
  return FILTER_MAP[filter].cssFilter;
}
