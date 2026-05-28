export function dismiss(when: () => boolean, delay: number) {
  let done: boolean = $state(false);

  $effect(() => {
    if (!when()) {
      done = false;
      return;
    }

    const cleanup = new AbortController();

    AbortSignal.timeout(delay).addEventListener(
      'abort',
      () => {
        if (cleanup.signal.aborted) {
          return;
        }
        done = true;
      },
      { signal: cleanup.signal },
    );

    return () => cleanup.abort();
  });

  return {
    get done() {
      return done;
    },
  };
}
