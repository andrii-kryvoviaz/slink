export type ImageOutputFormat = 'original' | 'png' | 'jpg' | 'webp' | 'avif';

export interface FormatOption {
  value: ImageOutputFormat;
  label: string;
  extension: string;
}

export const FORMAT_OPTIONS: FormatOption[] = [
  { value: 'original', label: 'Original', extension: '' },
  { value: 'png', label: 'PNG', extension: 'png' },
  { value: 'jpg', label: 'JPG', extension: 'jpg' },
  { value: 'webp', label: 'WebP', extension: 'webp' },
  { value: 'avif', label: 'AVIF', extension: 'avif' },
];

const FORMAT_ALIASES: Record<string, ImageOutputFormat> = {
  jpeg: 'jpg',
  jpg: 'jpg',
};

export const normalizeFormat = (format: string): ImageOutputFormat | null => {
  const lower = format.toLowerCase();
  if (FORMAT_ALIASES[lower]) return FORMAT_ALIASES[lower];
  const found = FORMAT_OPTIONS.find((opt) => opt.extension === lower);
  return found?.value ?? null;
};

export const isSameFormat = (format1: string, format2: string): boolean => {
  const norm1 = normalizeFormat(format1);
  const norm2 = normalizeFormat(format2);
  return norm1 !== null && norm1 === norm2;
};
