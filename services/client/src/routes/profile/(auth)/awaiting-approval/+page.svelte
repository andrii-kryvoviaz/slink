<script lang="ts">
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { Button, CopyContainer } from '@slink/components/UI/Action';

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

<section class="min-h-full">
  <div
    class="container mx-auto max-w-2xl px-6 py-16"
    in:fly={{ y: 20, duration: 400, delay: 150 }}
  >
    {#if data.status === 'inactive'}
      <div
        class="mb-8 flex justify-center"
        in:fade={{ duration: 300, delay: 300 }}
      >
        <div
          class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30"
        >
          <Icon
            icon="lucide:clock"
            class="h-8 w-8 text-amber-600 dark:text-amber-400"
          />
        </div>
      </div>

      <div class="text-center">
        <h1 class="mb-4 text-2xl font-semibold text-slate-900 dark:text-white">
          Account Pending Review
        </h1>

        <p class="mx-auto mb-8 max-w-lg text-slate-600 dark:text-slate-300">
          Your account is under review and will be activated once approved. On
          approval, the status on this page will change to "active". Please come
          back later for a follow-up.
        </p>

        <div
          class="mb-8 inline-flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 dark:border-amber-700 dark:bg-amber-900/20"
        >
          <div
            class="h-2.5 w-2.5 animate-pulse rounded-full bg-amber-500"
          ></div>
          <span
            class="text-sm font-medium uppercase tracking-wide text-amber-700 dark:text-amber-400"
          >
            pending
          </span>
        </div>

        <div
          class="rounded-lg border border-slate-200 bg-slate-50 p-6 dark:border-slate-700 dark:bg-slate-800"
        >
          <div class="mb-4">
            <h3
              class="mb-1 text-base font-medium text-slate-900 dark:text-white"
            >
              Reference ID
            </h3>
            <p class="text-xs text-slate-600 dark:text-slate-400">
              Provide this ID when contacting support
            </p>
          </div>
          <div class="flex justify-center">
            <CopyContainer value={data.userId} />
          </div>
        </div>
      </div>
    {/if}

    {#if data.status === 'active'}
      <div
        class="mb-8 flex justify-center"
        in:fade={{ duration: 300, delay: 300 }}
      >
        <div
          class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30"
        >
          <Icon
            icon="lucide:check-circle"
            class="h-8 w-8 text-emerald-600 dark:text-emerald-400"
          />
        </div>
      </div>

      <div class="text-center">
        <h1 class="mb-4 text-2xl font-semibold text-slate-900 dark:text-white">
          Account Activated
        </h1>

        <p class="mx-auto mb-8 max-w-lg text-slate-600 dark:text-slate-300">
          Welcome! Your account has been approved and is ready to use. Access
          all features and start managing your uploads.
        </p>

        <div
          class="mb-10 inline-flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 dark:border-emerald-700 dark:bg-emerald-900/20"
        >
          <div class="h-2.5 w-2.5 rounded-full bg-emerald-600"></div>
          <span
            class="text-sm font-medium uppercase tracking-wide text-emerald-700 dark:text-emerald-400"
          >
            activated
          </span>
        </div>

        <div
          class="rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 to-slate-100/50 p-8 dark:border-slate-700 dark:from-slate-800/50 dark:to-slate-700/30"
        >
          <div class="mb-6">
            <Icon
              icon="lucide:rocket"
              class="mx-auto h-12 w-12 text-emerald-600 dark:text-emerald-400"
            />
          </div>

          <h3 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">
            Ready to Get Started?
          </h3>

          <p class="mb-6 text-sm text-slate-600 dark:text-slate-300">
            Your account is now active. Sign in to start using the platform.
          </p>

          <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
            <Button
              href="/profile/login"
              variant="primary"
              size="lg"
              class="flex items-center gap-2"
            >
              <Icon icon="lucide:log-in" class="h-4 w-4" />
              Sign In
            </Button>
            <Button
              href="/explore"
              variant="outline"
              size="lg"
              class="flex items-center gap-2"
            >
              <Icon icon="lucide:compass" class="h-4 w-4" />
              Explore
            </Button>
          </div>
        </div>
      </div>
    {/if}
  </div>
</section>
