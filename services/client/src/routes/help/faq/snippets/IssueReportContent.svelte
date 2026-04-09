<script lang="ts">
  import { InfoCard } from '@slink/feature/Feedback/InfoCard';

  import { GITHUB } from '$lib/constants/app';
  import { t } from '$lib/i18n';
  import Icon from '@iconify/svelte';
</script>

<div class="space-y-6">
  <p class="text-slate-700 dark:text-slate-300">
    {$t('pages.faq.snippets.issue_report.intro')}
  </p>

  <InfoCard variant="neutral">
    {#snippet titleSnippet()}
      <div class="flex items-center gap-2">
        <Icon icon="ph:bug" class="w-4 h-4 text-red-500" />
        {$t('pages.faq.snippets.issue_report.before_reporting')}
      </div>
    {/snippet}

    <ul class="space-y-2">
      <li class="flex items-start gap-2">
        <Icon
          icon="ph:magnifying-glass"
          class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5"
        />
        <span>{$t('pages.faq.snippets.issue_report.check_existing')}</span>
      </li>
      <li class="flex items-start gap-2">
        <Icon
          icon="ph:browser"
          class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5"
        />
        <span>{$t('pages.faq.snippets.issue_report.try_other_browser')}</span>
      </li>
      <li class="flex items-start gap-2">
        <Icon
          icon="ph:info"
          class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5"
        />
        <span>{$t('pages.faq.snippets.issue_report.gather_details')}</span>
      </li>
    </ul>
  </InfoCard>

  <InfoCard
    variant="info"
    icon="ph:github-logo"
    title={$t('pages.faq.snippets.issue_report.report_on_github')}
  >
    <p class="mb-3">
      {$t('pages.faq.snippets.issue_report.github_description')}
    </p>
    <a
      href="https://github.com/{GITHUB.REPO_OWNER}/{GITHUB.REPO_NAME}/issues/new"
      target="_blank"
      rel="noopener noreferrer"
      class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors"
    >
      <Icon icon="ph:plus" class="w-4 h-4" />
      {$t('pages.faq.snippets.issue_report.create_issue')}
      <Icon icon="ph:arrow-square-out" class="w-3 h-3" />
    </a>
  </InfoCard>

  <InfoCard
    variant="purple"
    icon="ph:heart"
    title={$t('pages.faq.snippets.issue_report.thank_you')}
  >
    <p>
      {$t('pages.faq.snippets.issue_report.thank_you_description')}
    </p>
  </InfoCard>
</div>
