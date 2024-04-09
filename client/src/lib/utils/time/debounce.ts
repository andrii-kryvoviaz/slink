export function debounce(fn: Function, ms = 0) {
  let timeoutId: number;
  return async function (...args: any[]) {
    clearTimeout(timeoutId);
    // @ts-ignore
    timeoutId = setTimeout(() => fn.apply(this, args), ms);
  };
}
