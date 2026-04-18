<script lang="ts">
  import * as Share from '@slink/feature/Share';

  let { data } = $props();
</script>

{#if data.unavailable}
  <Share.Unavailable />
{/if}
