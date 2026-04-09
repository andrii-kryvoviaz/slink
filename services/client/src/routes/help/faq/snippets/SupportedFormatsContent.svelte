<script lang="ts">
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';

  import { t } from '$lib/i18n';

  const imageFormats = [
    'image/bmp',
    'image/png',
    'image/jpeg',
    'image/jpg',
    'image/gif',
    'image/webp',
    'image/svg+xml',
    'image/svg',
    'image/x-icon',
    'image/vnd.microsoft.icon',
    'image/x-tga',
    'image/avif',
  ];

  const convertedFormats = ['image/heic *', 'image/tiff *', 'image/tif *'];
</script>

<p>{$t('pages.faq.snippets.supported_formats.supported_mime_types')}</p>

<div class="mt-2 w-full flex flex-wrap gap-2">
  {#each imageFormats as format}
    <Badge variant="blue" outline>
      {format}
    </Badge>
  {/each}
  {#each convertedFormats as format}
    <Badge variant="amber" outline>
      {format}
    </Badge>
  {/each}
</div>

<p class="mt-4 text-xs">
  <span class="font-light"
    >{$t('pages.faq.snippets.supported_formats.converted_note')}</span
  >
</p>
