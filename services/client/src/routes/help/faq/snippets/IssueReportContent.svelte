<script lang="ts">
  import { InfoCard } from '@slink/feature/Feedback/InfoCard';

  import Icon from '@iconify/svelte';
</script>

<div class="space-y-6">
  <p class="text-slate-700 dark:text-slate-300">
    I appreciate your help in making Slink better! If you've encountered a bug
    or have suggestions for improvement, here's how you can help:
  </p>

  <InfoCard variant="neutral">
    {#snippet titleSnippet()}
      <div class="flex items-center gap-2">
        <Icon icon="ph:bug" class="w-4 h-4 text-red-500" />
        Before Reporting
      </div>
    {/snippet}

    <ul class="space-y-2">
      <li class="flex items-start gap-2">
        <Icon
          icon="ph:magnifying-glass"
          class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5"
        />
        <span>Check if the issue has already been reported</span>
      </li>
      <li class="flex items-start gap-2">
        <Icon
          icon="ph:browser"
          class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5"
        />
        <span>Try reproducing the issue in a different browser</span>
      </li>
      <li class="flex items-start gap-2">
        <Icon
          icon="ph:info"
          class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5"
        />
        <span
          >Gather relevant details (browser, steps to reproduce, screenshots)</span
        >
      </li>
    </ul>
  </InfoCard>

  <InfoCard variant="info" icon="ph:github-logo" title="Report on GitHub">
    <p class="mb-3">
      GitHub Issues is my primary platform for tracking bugs and feature
      requests. Your reports help me prioritize improvements.
    </p>
    <a
      href="https://github.com/andrii-kryvoviaz/slink/issues/new"
      target="_blank"
      rel="noopener noreferrer"
      class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors"
    >
      <Icon icon="ph:plus" class="w-4 h-4" />
      Create New Issue
      <Icon icon="ph:arrow-square-out" class="w-3 h-3" />
    </a>
  </InfoCard>

  <InfoCard variant="purple" icon="ph:heart" title="Thank You!">
    <p>
      Every report helps me improve Slink for the entire community. I review all
      issues and prioritize fixes based on impact and frequency.
    </p>
  </InfoCard>
</div>
