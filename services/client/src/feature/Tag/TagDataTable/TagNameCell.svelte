<script lang="ts">
  import { TagBadge } from '@slink/feature/Tag';

  import type { Tag } from '@slink/api/Resources/TagResource';

  interface Props {
    tag: Tag;
  }

  let { tag }: Props = $props();
</script>

<div class="flex flex-wrap items-center justify-between gap-2 min-w-0 w-full">
  <div class="flex flex-wrap items-center gap-2 min-w-0 flex-1">
    <TagBadge {tag} showCount={false} />
  </div>
</div>
