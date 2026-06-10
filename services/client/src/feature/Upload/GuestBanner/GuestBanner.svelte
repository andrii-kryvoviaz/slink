<script lang="ts">
  import {
    Banner,
    BannerAction,
    BannerContent,
    BannerIcon,
  } from '@slink/feature/Layout';

  interface Props {
    allowGuestUploads?: boolean;
  }

  let { allowGuestUploads = false }: Props = $props();
</script>

{#if allowGuestUploads}
  <Banner variant="info">
    {#snippet icon()}
      <BannerIcon variant="info" icon="ph:upload-simple" />
    {/snippet}
    {#snippet content()}
      <BannerContent
        title="Guest Upload"
        description="You can upload images without an account, but consider signing in to manage them"
      />
    {/snippet}
    {#snippet action()}
      <BannerAction variant="info" href="/profile/login" text="Sign In" />
    {/snippet}
  </Banner>
{:else}
  <Banner variant="warning">
    {#snippet icon()}
      <BannerIcon variant="warning" icon="ph:lock-simple" />
    {/snippet}
    {#snippet content()}
      <BannerContent
        title="Sign in to continue"
        description="Upload and manage your images securely"
      />
    {/snippet}
    {#snippet action()}
      <BannerAction
        variant="warning"
        href="/profile/login"
        text="Get Started"
      />
    {/snippet}
  </Banner>
{/if}
