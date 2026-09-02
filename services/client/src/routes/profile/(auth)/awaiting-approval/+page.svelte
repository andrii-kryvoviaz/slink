<script lang="ts">
  import {
    Banner,
    BannerAction,
    BannerContainer,
    BannerContent,
    BannerIcon,
  } from '@slink/feature/Layout';
  import { CopyableText } from '@slink/feature/Text';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();
</script>

<svelte:head>
  <title
    >{data.status === 'active' ? 'Account Approved' : 'Awaiting Approval'} | Slink</title
  >
</svelte:head>

{#if data.status === 'inactive' || data.status === 'suspended'}
  <div
    class="w-full max-w-4xl mx-auto px-6 py-8"
    in:fly={{ y: 20, duration: 500, delay: 100 }}
  >
    <div class="flex items-center justify-start gap-6 mb-8">
      <div
        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-warning/10 to-warning/5 border border-warning/10 flex items-center justify-center shadow-sm"
      >
        <Icon icon="lucide:hourglass" class="h-7 w-7 text-warning-strong" />
      </div>
      <div class="text-left">
        <h1 class="text-3xl font-bold text-foreground tracking-tight mb-2">
          Review in Progress
        </h1>
        <p class="text-foreground-muted text-base">
          Your account is currently under review
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div
        class="bg-gradient-to-br from-card/60 via-card/40 to-card/30 dark:from-card/40 dark:via-card/30 dark:to-card/20 backdrop-blur-md rounded-3xl border border-border/30 p-8 shadow-lg shadow-surface-inverse/5 dark:shadow-surface-inverse/20 h-full"
        in:fade={{ duration: 400, delay: 200 }}
      >
        <div class="flex items-start gap-4">
          <div
            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-info-solid/15 to-info-solid/8 shadow-md shadow-info/10 mt-1"
          >
            <Icon icon="lucide:clock" class="h-7 w-7 text-info" />
          </div>
          <div class="flex-1">
            <h3
              class="text-xl font-semibold text-foreground mb-4 tracking-tight"
            >
              What happens next?
            </h3>
            <p class="text-foreground-muted leading-relaxed text-base">
              Your account is being reviewed and will be activated once
              approved. Please check back later for updates on your account
              status.
            </p>
          </div>
        </div>
      </div>

      <div
        class="bg-gradient-to-br from-card/60 via-card/40 to-card/30 dark:from-card/40 dark:via-card/30 dark:to-card/20 backdrop-blur-md rounded-3xl border border-border/30 p-8 shadow-lg shadow-surface-inverse/5 dark:shadow-surface-inverse/20 h-full"
        in:fade={{ duration: 400, delay: 300 }}
      >
        <div class="flex items-start gap-4">
          <div
            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-accent-strong/15 to-accent-strong/7 shadow-md shadow-accent-strong/10 mt-1"
          >
            <Icon
              icon="lucide:help-circle"
              class="h-7 w-7 text-accent-strong"
            />
          </div>
          <div class="flex-1">
            <h3
              class="text-xl font-semibold text-foreground mb-4 tracking-tight"
            >
              Need help?
            </h3>
            <p class="text-foreground-muted mb-4 leading-relaxed text-base">
              Contact the administrator if you have questions about your account
              or the review process.
            </p>
          </div>
        </div>
      </div>
    </div>

    <BannerContainer class="mt-8">
      <Banner variant="info">
        {#snippet icon()}
          <BannerIcon variant="info" icon="lucide:id-card" />
        {/snippet}
        {#snippet content()}
          <BannerContent
            title="Your Account Reference"
            description="Provide this ID when contacting the administrator"
          />
        {/snippet}
        {#snippet action()}
          <div class="flex items-center">
            <CopyableText
              text={data.userId}
              class="text-sm font-mono font-semibold"
            />
          </div>
        {/snippet}
      </Banner>

      {#if page.data.globalSettings?.access?.allowUnauthenticatedAccess}
        <Banner variant="neutral">
          {#snippet icon()}
            <BannerIcon variant="neutral" icon="lucide:home" />
          {/snippet}
          {#snippet content()}
            <BannerContent
              title="Explore while you wait"
              description="Browse the platform features"
            />
          {/snippet}
          {#snippet action()}
            <BannerAction variant="neutral" href="/explore" text="Explore" />
          {/snippet}
        </Banner>
      {/if}
    </BannerContainer>
  </div>
{/if}

{#if data.status === 'active'}
  <div
    class="w-full max-w-4xl mx-auto px-6 py-8"
    in:fly={{ y: 20, duration: 500, delay: 100 }}
  >
    <div class="flex items-center justify-start gap-6 mb-8">
      <div
        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-success-strong/10 to-success-strong/5 border border-success-strong/10 flex items-center justify-center shadow-sm"
      >
        <Icon icon="lucide:check-circle" class="h-7 w-7 text-success-strong" />
      </div>
      <div class="text-left">
        <h1 class="text-3xl font-bold text-foreground tracking-tight mb-2">
          Welcome Aboard
        </h1>
        <p class="text-foreground-muted text-base">
          Your account has been approved and activated
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div
        class="bg-gradient-to-br from-card/60 via-card/40 to-card/30 dark:from-card/40 dark:via-card/30 dark:to-card/20 backdrop-blur-md rounded-3xl border border-border/30 p-8 shadow-lg shadow-surface-inverse/5 dark:shadow-surface-inverse/20 h-full"
        in:fade={{ duration: 400, delay: 200 }}
      >
        <div class="flex items-start gap-4">
          <div
            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-success-strong/15 to-success-strong/7 shadow-md shadow-success-strong/10 mt-1"
          >
            <Icon icon="lucide:sparkles" class="h-7 w-7 text-success-strong" />
          </div>
          <div class="flex-1">
            <h3
              class="text-xl font-semibold text-foreground mb-4 tracking-tight"
            >
              All Features Unlocked
            </h3>
            <p class="text-foreground-muted leading-relaxed text-base">
              Upload, share, and manage your content with full access to all
              platform features. Start creating and sharing your content.
            </p>
          </div>
        </div>
      </div>

      <div
        class="bg-gradient-to-br from-card/60 via-card/40 to-card/30 dark:from-card/40 dark:via-card/30 dark:to-card/20 backdrop-blur-md rounded-3xl border border-border/30 p-8 shadow-lg shadow-surface-inverse/5 dark:shadow-surface-inverse/20 h-full"
        in:fade={{ duration: 400, delay: 300 }}
      >
        <div class="flex items-start gap-4">
          <div
            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-info-solid/15 to-info-solid/8 shadow-md shadow-info/10 mt-1"
          >
            <Icon icon="lucide:rocket" class="h-7 w-7 text-info" />
          </div>
          <div class="flex-1">
            <h3
              class="text-xl font-semibold text-foreground mb-4 tracking-tight"
            >
              Ready to get started?
            </h3>
            <p class="text-foreground-muted leading-relaxed text-base">
              Sign in to your account to begin using all the platform features
              or explore the public gallery to see what others are sharing.
            </p>
          </div>
        </div>
      </div>
    </div>

    <BannerContainer class="mt-8">
      <Banner variant="info">
        {#snippet icon()}
          <BannerIcon variant="info" icon="lucide:log-in" />
        {/snippet}
        {#snippet content()}
          <BannerContent
            title="Ready to continue"
            description="Sign in to access all platform features"
          />
        {/snippet}
        {#snippet action()}
          <BannerAction variant="info" href="/profile/login" text="Sign In" />
        {/snippet}
      </Banner>
    </BannerContainer>
  </div>
{/if}
