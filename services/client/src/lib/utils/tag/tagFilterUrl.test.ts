import { describe, expect, it } from 'vitest';

import { TagFilterUrlManager } from '@slink/utils/tag/tagFilterUrl';

const managerFor = (search: string) =>
  TagFilterUrlManager.fromPageUrl(new URL(`http://localhost/history${search}`));

describe('TagFilterUrlManager.hasTagFilter', () => {
  it('reports a filter when the url carries tag ids', () => {
    expect(managerFor('?tagIds=a,b').hasTagFilter()).toBe(true);
  });

  it('reports no filter when tagIds is absent, blank or only separators', () => {
    for (const search of ['', '?tagIds=', '?tagIds=,,']) {
      expect(managerFor(search).hasTagFilter()).toBe(false);
    }
  });
});
