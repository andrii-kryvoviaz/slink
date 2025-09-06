<script lang="ts">
  import { InfoCard } from '@slink/feature/Feedback/InfoCard';

  import Icon from '@iconify/svelte';
</script>

<div class="space-y-6">
  <p class="text-slate-700 dark:text-slate-300">
    Slink offers a
    <a
      href="/explore"
      target="_blank"
      class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 underline underline-offset-2 font-medium transition-colors"
    >
      public gallery
    </a>, where people can share their images with others.
  </p>

  <InfoCard variant="neutral" title="Visibility Options">
    <div class="space-y-3">
      <div class="flex items-start gap-3">
        <div class="flex-shrink-0 mt-0.5">
          <Icon
            icon="ph:eye"
            class="w-5 h-5 text-emerald-600 dark:text-emerald-400"
          />
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1">
            <span
              class="text-sm font-semibold text-slate-900 dark:text-slate-100"
              >Public</span
            >
          </div>
          <p class="text-sm text-slate-600 dark:text-slate-400">
            Images are shared in the public gallery and visible to all users
          </p>
        </div>
      </div>

      <div class="flex items-start gap-3">
        <div class="flex-shrink-0 mt-0.5">
          <Icon
            icon="ph:eye-slash"
            class="w-5 h-5 text-slate-500 dark:text-slate-400"
          />
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1">
            <span
              class="text-sm font-semibold text-slate-900 dark:text-slate-100"
              >Private</span
            >
            <span
              class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300"
            >
              Default
            </span>
          </div>
          <p class="text-sm text-slate-600 dark:text-slate-400">
            Images are not shared in the public gallery and only accessible by
            the owner
          </p>
        </div>
      </div>
    </div>
  </InfoCard>

  <InfoCard variant="warning" icon="ph:warning" title="Important Note">
    <p>
      Raw images are accessible to anyone with a direct link, regardless of
      visibility setting.
    </p>
  </InfoCard>
</div>
