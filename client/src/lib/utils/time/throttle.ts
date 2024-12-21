export function throttle(fn: Function, ms = 0) {
  let lastCall = 0;
  return async function (...args: any[]) {
    const now = Date.now();
    if (now - lastCall < ms) return;
    lastCall = now;

    // @ts-ignore
    return fn.apply(this, args);
  };
}
