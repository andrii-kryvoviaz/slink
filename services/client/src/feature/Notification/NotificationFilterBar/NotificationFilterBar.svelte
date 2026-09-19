<script lang="ts">
  import { ToggleGroup } from '@slink/ui/components';
  import type { ToggleGroupOption } from '@slink/ui/components';

  import {
    type NotificationFilterId,
    NotificationFilters,
  } from '@slink/utils/notification';

  import { notificationFilterBarTheme } from './NotificationFilterBar.theme';

  interface Props {
    value: NotificationFilterId;
    onChange: (id: NotificationFilterId) => void;
  }

  let { value, onChange }: Props = $props();

  const theme = notificationFilterBarTheme();

  const options = $derived(
    NotificationFilters.list.map(
      (filter): ToggleGroupOption<NotificationFilterId> => ({
        value: filter.id,
        label: filter.label,
      }),
    ),
  );
</script>

<div class={theme.root()}>
  <ToggleGroup
    {value}
    {options}
    onValueChange={onChange}
    size="sm"
    rounded="full"
    aria-label="Filter notifications"
  />
</div>
