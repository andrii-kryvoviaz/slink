<script lang="ts">
  import CopyableText from '@slink/feature/Text/CopyableText/CopyableText.svelte';

  interface Props {
    username: string;
  }

  let { username }: Props = $props();
</script>

<div class="font-mono text-sm text-gray-900 dark:text-gray-100">
  <CopyableText text={username} />
</div>
