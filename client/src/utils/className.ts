import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

import type { ClassValue } from 'clsx';

export function className(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}
