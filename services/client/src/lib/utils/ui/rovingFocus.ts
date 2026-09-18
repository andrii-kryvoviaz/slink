import { Key } from './keyboard.js';

const PREVIOUS_KEYS = new Set<string>([Key.ArrowLeft, Key.ArrowUp]);
const NEXT_KEYS = new Set<string>([Key.ArrowRight, Key.ArrowDown]);

export function getNextRovingIndex(
  key: string,
  currentIndex: number,
  count: number,
): number | null {
  if (count <= 0) {
    return null;
  }

  if (key === Key.Home) {
    return 0;
  }

  if (key === Key.End) {
    return count - 1;
  }

  if (PREVIOUS_KEYS.has(key)) {
    if (currentIndex === 0) {
      return count - 1;
    }
    return currentIndex - 1;
  }

  if (NEXT_KEYS.has(key)) {
    if (currentIndex === count - 1) {
      return 0;
    }
    return currentIndex + 1;
  }

  return null;
}
