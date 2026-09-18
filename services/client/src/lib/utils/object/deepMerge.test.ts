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
});
