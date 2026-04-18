<script lang="ts">
  import * as Share from '@slink/feature/Share';

  let { data } = $props();

  const ogMeta = $derived(data.ogMeta);
</script>

<svelte:head>
  {#if ogMeta}
    <meta property="og:type" content="website" />
    <meta property="og:url" content={ogMeta.url} />
    <meta property="og:title" content={ogMeta.title} />
    <meta property="og:description" content={ogMeta.description} />
    <meta property="og:image" content={ogMeta.imageUrl} />
    <meta property="og:image:type" content={ogMeta.mimeType} />
    {#if ogMeta.width}
      <meta property="og:image:width" content={ogMeta.width} />
    {/if}
    {#if ogMeta.height}
      <meta property="og:image:height" content={ogMeta.height} />
    {/if}
    <meta name="twitter:card" content="summary_large_image" />
  {/if}
</svelte:head>

{#if data.unavailable}
  <Share.Unavailable />
{/if}
