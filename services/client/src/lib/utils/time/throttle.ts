export function throttle<T extends (...args: never[]) => unknown>(
  fn: T,
  ms = 0,
) {
  let lastCall = 0;
  return function (this: ThisParameterType<T>, ...args: Parameters<T>) {
    const now = Date.now();
    if (now - lastCall < ms) return;
    lastCall = now;
    return fn.apply(this, args);
  };
}
