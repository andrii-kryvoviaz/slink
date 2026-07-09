const PREFERRED_ORDER = ['jpeg', 'png', 'gif', 'webp'];
const VISIBLE_COUNT = 6;

const rank = (format: string): number => {
  const index = PREFERRED_ORDER.indexOf(format.toLowerCase());
  if (index === -1) return PREFERRED_ORDER.length;
  return index;
};

class SupportedFormats {
  #source: () => string[] = $state(() => []);
  #expanded = $state(false);

  #ordered = $derived([...this.#source()].sort((a, b) => rank(a) - rank(b)));
  #hiddenCount = $derived(Math.max(0, this.#ordered.length - VISIBLE_COUNT));
  #visible = $derived.by(() => {
    if (this.#expanded || this.#hiddenCount === 0) return this.#ordered;
    return this.#ordered.slice(0, VISIBLE_COUNT);
  });

  constructor(source: () => string[]) {
    this.#source = source;
  }

  get all() {
    return this.#ordered;
  }

  get visible() {
    return this.#visible;
  }

  get hiddenCount() {
    return this.#hiddenCount;
  }

  get expanded() {
    return this.#expanded;
  }

  toggle(): void {
    this.#expanded = !this.#expanded;
  }
}

export function useSupportedFormats(formats: () => string[]) {
  return new SupportedFormats(formats);
}
