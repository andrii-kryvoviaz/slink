<script lang="ts">
  import { TagDeletePopover, TagMoveDialog } from '@slink/feature/Tag';

  import type { Tag } from '@slink/api/Resources/TagResource';

  interface Props {
    tag: Tag;
    onDelete: (tag: Tag) => Promise<void>;
    onMove: (tag: Tag, parentId?: string | null) => Promise<void>;
  }

  let { tag, onDelete, onMove }: Props = $props();
</script>

<div class="flex items-center justify-end gap-2 pr-2">
  <TagMoveDialog {tag} {onMove} />
  <TagDeletePopover {tag} {onDelete} />
</div>
