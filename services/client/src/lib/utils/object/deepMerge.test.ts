import { describe, expect, it } from 'vitest';

import { deepMerge } from '@slink/utils/object/deepMerge';

type Obj = Record<string, unknown>;

describe('deepMerge', () => {
  it('merges nested objects recursively', () => {
    const target: Obj = { a: { x: 1, y: 2 }, keep: true };
    const source: Obj = { a: { y: 3, z: 4 } };

    expect(deepMerge(target, source)).toEqual({
      a: { x: 1, y: 3, z: 4 },
      keep: true,
    });

    const deepTarget: Obj = { a: { b: { c: 1, d: 1 } } };
    const deepSource: Obj = { a: { b: { d: 2 } } };

    expect(deepMerge(deepTarget, deepSource)).toEqual({
      a: { b: { c: 1, d: 2 } },
    });
  });

  it('source scalars overwrite target scalars', () => {
    const target = { n: 1, s: 'a', b: true };
    const source = { n: 2, s: 'b', b: false };

    expect(deepMerge(target, source)).toEqual({ n: 2, s: 'b', b: false });
  });

  it('arrays are replaced, not merged', () => {
    const target = { list: [1, 2, 3] };
    const source = { list: [9] };

    expect(deepMerge(target, source)).toEqual({ list: [9] });

    const nestedTarget = { a: { list: ['x'] } };
    const nestedSource = { a: { list: [] } };

    expect(deepMerge(nestedTarget, nestedSource)).toEqual({
      a: { list: [] },
    });
  });

  it('does not mutate target', () => {
    const target: Obj = { a: { x: 1, y: 2 }, keep: true };
    const snapshot = structuredClone(target);
    const source: Obj = { a: { y: 3 } };

    const result = deepMerge(target, source);

    expect(target).toEqual(snapshot);
    expect(target.a).toEqual(snapshot.a);
    expect(result).not.toBe(target);
  });

  it('accepts a deep-partial source and returns the target type', () => {
    type Shape = { a: { b: number; c: number } };
    const target: Shape = { a: { b: 1, c: 1 } };

    const result: Shape = deepMerge<Shape>(target, { a: { c: 2 } });

    expect(result).toEqual({ a: { b: 1, c: 2 } });
  });

  it('leaves keys absent from the source untouched', () => {
    const target = { a: { b: 1, c: 1 }, d: 'keep', e: [1, 2] };

    const result = deepMerge(target, { a: { c: 2 } });

    expect(result).toEqual({ a: { b: 1, c: 2 }, d: 'keep', e: [1, 2] });
  });

  it('a source of scalars, arrays and null equals a shallow spread', () => {
    const target: Obj = { a: 1, b: [1] };
    const source: Obj = { a: 2, b: [3], c: null };

    expect(deepMerge(target, source)).toEqual({ ...target, ...source });
    expect(deepMerge(target, source)).toEqual({ a: 2, b: [3], c: null });
  });

  it('a nested object source still deep-merges', () => {
    const target = { attributes: { isPublic: false, views: 3 } };

    expect(deepMerge(target, { attributes: { isPublic: true } })).toEqual({
      attributes: { isPublic: true, views: 3 },
    });
  });

  it('leaves target unchanged for an empty deep-partial source', () => {
    const target: Obj = { a: { x: 1, y: 2 }, keep: true };

    const result = deepMerge(target, {});

    expect(result).toEqual(target);
    expect(result).not.toBe(target);
  });
});
