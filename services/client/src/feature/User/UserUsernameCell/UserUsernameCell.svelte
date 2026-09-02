<script lang="ts">
  import CopyableText from '@slink/feature/Text/CopyableText/CopyableText.svelte';

  interface Props {
    username: string | null;
  }

  let { username }: Props = $props();
</script>

<div class="font-mono text-sm text-foreground">
  <CopyableText text={username} />
</div>
