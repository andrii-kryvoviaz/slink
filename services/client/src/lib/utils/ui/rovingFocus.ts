const PREVIOUS_KEYS = new Set(['ArrowLeft', 'ArrowUp']);
const NEXT_KEYS = new Set(['ArrowRight', 'ArrowDown']);

export function getNextRovingIndex(
  key: string,
  currentIndex: number,
  count: number,
): number | null {
  if (count <= 0) {
    return null;
  }

  if (key === 'Home') {
    return 0;
  }

  if (key === 'End') {
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
