<script module lang="ts">
  export function isExifNoticeVisible(
    stripExifMetadata: boolean,
    exifOverride: 'default' | 'strip' | 'keep',
    hidden: boolean,
  ): boolean {
    const metadataKept =
      exifOverride === 'default' ? !stripExifMetadata : exifOverride === 'keep';
    return metadataKept && !hidden;
  }
</script>

<script lang="ts">
  import { Banner, BannerContent, BannerIcon } from '@slink/feature/Layout';
  import { Link } from '@slink/ui/components/link';

  import { page } from '$app/state';

  interface Props {
    stripExifMetadata: boolean;
    exifOverride: 'default' | 'strip' | 'keep';
  }

  let { stripExifMetadata, exifOverride }: Props = $props();

  const { settings } = page.data;

  const visible = $derived(
    isExifNoticeVisible(
      stripExifMetadata,
      exifOverride,
      settings.banners.hideExifKeptNotice,
    ),
  );

  function dismiss(): void {
    settings.banners = { ...settings.banners, hideExifKeptNotice: true };
  }
</script>

{#if visible}
  <Banner variant="info" onDismiss={dismiss} dismissLabel="Don't show again">
    {#snippet icon()}
      <BannerIcon variant="info" icon="ph:map-pin" />
    {/snippet}
    {#snippet content()}
      <BannerContent title="Metadata kept on uploads">
        Location, camera and other EXIF metadata is preserved in your uploads.
        <Link
          href="/preferences"
          class="font-medium text-blue-600 underline decoration-blue-600/40 underline-offset-2 transition-colors hover:text-blue-700 hover:decoration-blue-600/80 dark:text-blue-300 dark:decoration-blue-300/40 dark:hover:text-blue-200"
          >Adjust in preferences</Link
        >
      </BannerContent>
    {/snippet}
  </Banner>
{/if}
