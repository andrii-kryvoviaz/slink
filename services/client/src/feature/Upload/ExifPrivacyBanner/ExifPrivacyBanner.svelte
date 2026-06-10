<script lang="ts">
  import { Banner, BannerContent, BannerIcon } from '@slink/feature/Layout';
  import { Link } from '@slink/ui/components/link';

  import { useExifNotice } from './ExifNoticeState.svelte';

  const notice = useExifNotice();
</script>

{#if notice.visible}
  <Banner
    variant="info"
    onDismiss={() => notice.dismiss()}
    dismissLabel="Don't show again"
  >
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
