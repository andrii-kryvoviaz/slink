<script lang="ts">
  import { InfoCard } from '@slink/feature/Feedback/InfoCard';

  import { t } from '$lib/i18n';
  import Icon from '@iconify/svelte';
</script>

<div class="space-y-6">
  <p class="text-slate-700 dark:text-slate-300">
    {$t('pages.faq.snippets.image_visibility.slink_offers')}
    <a
      href="/explore"
      target="_blank"
      class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 underline underline-offset-2 font-medium transition-colors"
    >
      {$t('pages.faq.snippets.image_visibility.public_gallery')}
    </a>{$t('pages.faq.snippets.image_visibility.where_people_share')}
  </p>

  <InfoCard
    variant="neutral"
    title={$t('pages.faq.snippets.image_visibility.visibility_options')}
  >
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
              >{$t('pages.faq.snippets.image_visibility.public')}</span
            >
          </div>
          <p class="text-sm text-slate-600 dark:text-slate-400">
            {$t('pages.faq.snippets.image_visibility.public_description')}
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
              >{$t('pages.faq.snippets.image_visibility.private')}</span
            >
            <span
              class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300"
            >
              {$t('pages.faq.snippets.image_visibility.default')}
            </span>
          </div>
          <p class="text-sm text-slate-600 dark:text-slate-400">
            {$t('pages.faq.snippets.image_visibility.private_description')}
          </p>
        </div>
      </div>
    </div>
  </InfoCard>

  <InfoCard
    variant="warning"
    icon="ph:warning"
    title={$t('pages.faq.snippets.image_visibility.important_note')}
  >
    <p>
      {$t('pages.faq.snippets.image_visibility.raw_images_note')}
    </p>
  </InfoCard>
</div>
