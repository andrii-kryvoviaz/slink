import {
  type DayKind,
  calendarDayKey,
  dayKind,
  startOfDay,
} from '$lib/utils/date.svelte';

import type { NotificationGroup } from './NotificationGrouping';

export interface NotificationDayBucket {
  key: string;
  day: Date;
  kind: DayKind;
  groups: NotificationGroup[];
}

export class NotificationDayBuckets {
  static fromGroups(
    groups: NotificationGroup[],
    now: Date = new Date(),
  ): NotificationDayBucket[] {
    const buckets = new Map<string, NotificationDayBucket>();

    for (const group of groups) {
      const moment = new Date(group.latestTimestamp * 1000);
      const key = calendarDayKey(moment);

      if (!buckets.has(key)) {
        const day = startOfDay(moment);
        buckets.set(key, { key, day, kind: dayKind(day, now), groups: [] });
      }

      buckets.get(key)!.groups.push(group);
    }

    return [...buckets.values()];
  }
}
