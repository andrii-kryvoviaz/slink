<script lang="ts">
  import { InfoCard } from '@slink/feature/Feedback/InfoCard';

  import { GITHUB } from '$lib/constants/app';
  import { t } from '$lib/i18n';
  import Icon from '@iconify/svelte';
</script>

<div class="space-y-6">
  <p>
    {$t('pages.faq.snippets.share_feature.intro')}
  </p>

  <InfoCard variant="neutral">
    {#snippet titleSnippet()}
      <div class="flex items-center gap-2">
        <Icon icon="ph:lightbulb" class="w-4 h-4 text-amber-500" />
        {$t('pages.faq.snippets.share_feature.current_options')}
      </div>
    {/snippet}

    <ul class="space-y-2">
      <li class="flex items-center gap-2">
        <Icon icon="ph:link" class="w-4 h-4 text-slate-400" />
        {$t('pages.faq.snippets.share_feature.direct_link')}
      </li>
      <li class="flex items-center gap-2">
        <Icon icon="ph:globe" class="w-4 h-4 text-slate-400" />
        {$t('pages.faq.snippets.share_feature.public_gallery')}
      </li>
      <li class="flex items-center gap-2">
        <Icon icon="ph:eye" class="w-4 h-4 text-slate-400" />
        {$t('pages.faq.snippets.share_feature.link_access')}
      </li>
    </ul>
  </InfoCard>

  <InfoCard
    variant="info"
    icon="ph:rocket"
    title={$t('pages.faq.snippets.share_feature.want_more_control')}
  >
    <p class="mb-3">
      {$t('pages.faq.snippets.share_feature.roadmap_intro')}
    </p>
    <ul class="space-y-1 mb-3">
      <li class="flex items-center gap-2">
        <Icon icon="ph:clock" class="w-3 h-3" />
        {$t('pages.faq.snippets.share_feature.expiration_dates')}
      </li>
      <li class="flex items-center gap-2">
        <Icon icon="ph:lock" class="w-3 h-3" />
        {$t('pages.faq.snippets.share_feature.password_protection')}
      </li>
      <li class="flex items-center gap-2">
        <Icon icon="ph:users" class="w-3 h-3" />
        {$t('pages.faq.snippets.share_feature.private_group_sharing')}
      </li>
    </ul>
    <a
      href="https://github.com/{GITHUB.REPO_OWNER}/{GITHUB.REPO_NAME}/issues/7"
      target="_blank"
      rel="noopener noreferrer"
      class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors"
    >
      <Icon icon="ph:heart" class="w-4 h-4" />
      {$t('pages.faq.snippets.share_feature.support_feature_request')}
      <Icon icon="ph:arrow-square-out" class="w-3 h-3" />
    </a>
  </InfoCard>
</div>
