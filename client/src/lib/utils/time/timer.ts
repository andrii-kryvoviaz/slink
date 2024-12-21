type Timer = {
  pause: () => void;
  resume: () => void;
  clear: () => void;
};

export function createTimer(callback: () => void, delay: number): Timer {
  let remaining = delay;
  let timerId: number | undefined;
  let start: number | undefined;

  function pause() {
    window.clearTimeout(timerId);
    timerId = undefined;
    remaining -= Date.now() - (start || 0);
  }

  function resume() {
    if (timerId !== undefined) {
      return;
    }

    start = Date.now();
    timerId = window.setTimeout(() => {
      pause();
      callback();
    }, remaining);
  }

  function clear() {
    if (timerId !== undefined) {
      window.clearTimeout(timerId);
    }

    remaining = 0;
  }

  resume();

  return { pause, resume, clear };
}
