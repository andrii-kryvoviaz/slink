export type FractionOption = {
  label: string;
  value: number;
};

export type FractionPickerSize = 'sm' | 'md' | 'lg';

export interface FractionPickerProps {
  currentWidth: number;
  currentHeight: number;
  originalWidth: number;
  originalHeight: number;
  options?: FractionOption[];
  size?: FractionPickerSize;
  disabled?: boolean;
  on?: {
    change: (value: number) => void;
  };
}

export const DEFAULT_FRACTION_OPTIONS: FractionOption[] = [
  { label: '1:1', value: 1 },
  { label: '1:2', value: 0.5 },
  { label: '1:3', value: 1 / 3 },
  { label: '1:4', value: 0.25 },
];
