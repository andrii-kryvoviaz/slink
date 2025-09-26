<script lang="ts">
  import type { Tag } from '@slink/api/Resources/TagResource';

  import TagDeletePopover from '../TagDeleteConfirmation/TagDeletePopover.svelte';

  interface Props {
    tag: Tag;
    onDelete: (tag: Tag) => Promise<void>;
  }

  let { tag, onDelete }: Props = $props();
</script>

<div class="flex items-center justify-end gap-2 pr-2">
  <TagDeletePopover {tag} {onDelete} />
</div>
