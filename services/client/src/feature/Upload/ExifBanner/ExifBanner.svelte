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
        {#if notice.canAdjustPreferences}
          <span class="mt-1 block">
            <Link
              href="/preferences"
              class="font-medium text-info underline decoration-info/40 underline-offset-2 transition-colors hover:text-info-text hover:decoration-info/80"
              >Adjust in preferences</Link
            >
          </span>
        {/if}
      </BannerContent>
    {/snippet}
  </Banner>
{/if}
