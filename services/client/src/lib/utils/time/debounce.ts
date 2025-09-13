export function debounce<T extends (...args: never[]) => unknown>(
  fn: T,
  ms = 0,
) {
  let timeoutId: ReturnType<typeof setTimeout>;

  const debouncedFn = function (
    this: ThisParameterType<T>,
    ...args: Parameters<T>
  ) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), ms);
  } as T & { cancel: () => void };

  debouncedFn.cancel = () => {
    clearTimeout(timeoutId);
  };

  return debouncedFn;
}
