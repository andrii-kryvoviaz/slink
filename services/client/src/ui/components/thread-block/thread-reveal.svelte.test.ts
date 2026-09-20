import { describe, expect, it } from 'vitest';

import { ThreadReveal } from './thread-reveal.svelte';

const reveal = (total: number, pageSize = 8) =>
  new ThreadReveal(
    () => pageSize,
    () => total,
  );

describe('ThreadReveal', () => {
  it('shows every row when the list fits in one page', () => {
    const state = reveal(5);

    expect(state.visible).toBe(5);
    expect(state.remaining).toBe(0);
    expect(state.next).toBe(0);
  });

  it('caps the first page at the page size', () => {
    const state = reveal(20);

    expect(state.visible).toBe(8);
    expect(state.remaining).toBe(12);
    expect(state.next).toBe(8);
  });

  it('reveals one page per step and caps the last step at the remainder', () => {
    const state = reveal(20);

    state.showMore();
    expect(state.visible).toBe(16);
    expect(state.next).toBe(4);

    state.showMore();
    expect(state.visible).toBe(20);
    expect(state.remaining).toBe(0);
    expect(state.next).toBe(0);
  });

  it('honours a custom page size', () => {
    const state = reveal(7, 3);

    expect(state.visible).toBe(3);
    expect(state.next).toBe(3);
  });

  it('returns to one page on reset', () => {
    const state = reveal(20);

    state.showMore();
    state.reset();

    expect(state.visible).toBe(8);
    expect(state.remaining).toBe(12);
  });
});
