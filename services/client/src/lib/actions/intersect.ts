import type { Action } from 'svelte/action';

interface IntersectConfig {
  enabled: boolean;
  onEnter: () => void;
}

export const intersect: Action<HTMLElement, IntersectConfig> = (
  node,
  params,
) => {
  let current = params;

  const observer = new IntersectionObserver((entries) => {
    for (const entry of entries) {
      if (entry.isIntersecting && current.enabled) {
        current.onEnter();
      }
    }
  });

  observer.observe(node);

  return {
    update(next: IntersectConfig) {
      current = next;
    },
    destroy() {
      observer.disconnect();
    },
  };
};
