export function useAutoReset(delay: number) {
  let active = $state(false);

  $effect(() => {
    if (active) {
      const timeoutId = setTimeout(() => (active = false), delay);
      return () => clearTimeout(timeoutId);
    }
  });

  return {
    get active() {
      return active;
    },
    trigger() {
      active = true;
    },
    reset() {
      active = false;
    },
  };
}
