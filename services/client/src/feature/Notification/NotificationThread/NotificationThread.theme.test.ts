import { describe, expect, it } from 'vitest';

import { notificationThread } from './NotificationThread.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('notificationThread', () => {
  it('strips the hashtag chip vertical padding in the text slot', () => {
    const text = tokens(notificationThread().text());

    expect(text).toEqual(expect.arrayContaining(['[&_[data-hashtag]]:py-0']));
  });
});
