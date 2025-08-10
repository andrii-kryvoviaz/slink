<script lang="ts">
  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { CopyableText } from '@slink/components/UI/Action';
  import {
    Banner,
    BannerAction,
    BannerContent,
    BannerIcon,
  } from '@slink/components/UI/Banner';

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
        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500/10 to-amber-500/5 border border-amber-500/10 flex items-center justify-center shadow-sm"
      >
        <Icon
          icon="lucide:hourglass"
          class="h-7 w-7 text-amber-600 dark:text-amber-400"
        />
      </div>
      <div class="text-left">
        <h1
          class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight mb-2"
        >
          Review in Progress
        </h1>
        <p class="text-gray-600 dark:text-gray-400 text-base">
          Your account is currently under review
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div
        class="bg-gradient-to-br from-white/60 via-white/40 to-white/30 dark:from-gray-900/40 dark:via-gray-900/30 dark:to-gray-900/20 backdrop-blur-md rounded-3xl border border-gray-200/30 dark:border-gray-700/20 p-8 shadow-lg shadow-gray-900/5 dark:shadow-gray-900/20 h-full"
        in:fade={{ duration: 400, delay: 200 }}
      >
        <div class="flex items-start gap-4">
          <div
            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900/40 dark:to-blue-900/20 shadow-md shadow-blue-500/10 mt-1"
          >
            <Icon
              icon="lucide:clock"
              class="h-7 w-7 text-blue-600 dark:text-blue-400"
            />
          </div>
          <div class="flex-1">
            <h3
              class="text-xl font-semibold text-gray-900 dark:text-white mb-4 tracking-tight"
            >
              What happens next?
            </h3>
            <p
              class="text-gray-600 dark:text-gray-400 leading-relaxed text-base"
            >
              Your account is being reviewed and will be activated once
              approved. Please check back later for updates on your account
              status.
            </p>
          </div>
        </div>
      </div>

      <div
        class="bg-gradient-to-br from-white/60 via-white/40 to-white/30 dark:from-gray-900/40 dark:via-gray-900/30 dark:to-gray-900/20 backdrop-blur-md rounded-3xl border border-gray-200/30 dark:border-gray-700/20 p-8 shadow-lg shadow-gray-900/5 dark:shadow-gray-900/20 h-full"
        in:fade={{ duration: 400, delay: 300 }}
      >
        <div class="flex items-start gap-4">
          <div
            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-100 to-purple-50 dark:from-purple-900/40 dark:to-purple-900/20 shadow-md shadow-purple-500/10 mt-1"
          >
            <Icon
              icon="lucide:help-circle"
              class="h-7 w-7 text-purple-600 dark:text-purple-400"
            />
          </div>
          <div class="flex-1">
            <h3
              class="text-xl font-semibold text-gray-900 dark:text-white mb-4 tracking-tight"
            >
              Need help?
            </h3>
            <p
              class="text-gray-600 dark:text-gray-400 mb-4 leading-relaxed text-base"
            >
              Contact the administrator if you have questions about your account
              or the review process.
            </p>
          </div>
        </div>
      </div>
    </div>

    <Banner variant="info" class="mt-8">
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
      <Banner variant="neutral" class="mt-4">
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
  </div>
{/if}

{#if data.status === 'active'}
  <div
    class="w-full max-w-4xl mx-auto px-6 py-8"
    in:fly={{ y: 20, duration: 500, delay: 100 }}
  >
    <div class="flex items-center justify-start gap-6 mb-8">
      <div
        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-emerald-500/5 border border-emerald-500/10 flex items-center justify-center shadow-sm"
      >
        <Icon
          icon="lucide:check-circle"
          class="h-7 w-7 text-emerald-600 dark:text-emerald-400"
        />
      </div>
      <div class="text-left">
        <h1
          class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight mb-2"
        >
          Welcome Aboard
        </h1>
        <p class="text-gray-600 dark:text-gray-400 text-base">
          Your account has been approved and activated
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div
        class="bg-gradient-to-br from-white/60 via-white/40 to-white/30 dark:from-gray-900/40 dark:via-gray-900/30 dark:to-gray-900/20 backdrop-blur-md rounded-3xl border border-gray-200/30 dark:border-gray-700/20 p-8 shadow-lg shadow-gray-900/5 dark:shadow-gray-900/20 h-full"
        in:fade={{ duration: 400, delay: 200 }}
      >
        <div class="flex items-start gap-4">
          <div
            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-50 dark:from-emerald-900/40 dark:to-emerald-900/20 shadow-md shadow-emerald-500/10 mt-1"
          >
            <Icon
              icon="lucide:sparkles"
              class="h-7 w-7 text-emerald-600 dark:text-emerald-400"
            />
          </div>
          <div class="flex-1">
            <h3
              class="text-xl font-semibold text-gray-900 dark:text-white mb-4 tracking-tight"
            >
              All Features Unlocked
            </h3>
            <p
              class="text-gray-600 dark:text-gray-400 leading-relaxed text-base"
            >
              Upload, share, and manage your content with full access to all
              platform features. Start creating and sharing your content.
            </p>
          </div>
        </div>
      </div>

      <div
        class="bg-gradient-to-br from-white/60 via-white/40 to-white/30 dark:from-gray-900/40 dark:via-gray-900/30 dark:to-gray-900/20 backdrop-blur-md rounded-3xl border border-gray-200/30 dark:border-gray-700/20 p-8 shadow-lg shadow-gray-900/5 dark:shadow-gray-900/20 h-full"
        in:fade={{ duration: 400, delay: 300 }}
      >
        <div class="flex items-start gap-4">
          <div
            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900/40 dark:to-blue-900/20 shadow-md shadow-blue-500/10 mt-1"
          >
            <Icon
              icon="lucide:rocket"
              class="h-7 w-7 text-blue-600 dark:text-blue-400"
            />
          </div>
          <div class="flex-1">
            <h3
              class="text-xl font-semibold text-gray-900 dark:text-white mb-4 tracking-tight"
            >
              Ready to get started?
            </h3>
            <p
              class="text-gray-600 dark:text-gray-400 leading-relaxed text-base"
            >
              Sign in to your account to begin using all the platform features
              or explore the public gallery to see what others are sharing.
            </p>
          </div>
        </div>
      </div>
    </div>

    <Banner variant="info" class="mt-8">
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
  </div>
{/if}
