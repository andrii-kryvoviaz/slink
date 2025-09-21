<script lang="ts">
  import { InfoCard } from '@slink/feature/Feedback/InfoCard';

  import { GITHUB } from '$lib/constants/app';
  import Icon from '@iconify/svelte';
</script>

<div class="space-y-6">
  <p>
    You can easily share your images with others using the direct link provided
    for each uploaded image.
  </p>

  <InfoCard variant="neutral">
    {#snippet titleSnippet()}
      <div class="flex items-center gap-2">
        <Icon icon="ph:lightbulb" class="w-4 h-4 text-amber-500" />
        Current Sharing Options
      </div>
    {/snippet}

    <ul class="space-y-2">
      <li class="flex items-center gap-2">
        <Icon icon="ph:link" class="w-4 h-4 text-slate-400" />
        Direct link sharing with anyone
      </li>
      <li class="flex items-center gap-2">
        <Icon icon="ph:globe" class="w-4 h-4 text-slate-400" />
        Public gallery visibility (optional)
      </li>
      <li class="flex items-center gap-2">
        <Icon icon="ph:eye" class="w-4 h-4 text-slate-400" />
        Link accessible to anyone who has it
      </li>
    </ul>
  </InfoCard>

  <InfoCard variant="info" icon="ph:rocket" title="Want More Control?">
    <p class="mb-3">
      I'm working on advanced sharing features that will give you more control
      over your image sharing:
    </p>
    <ul class="space-y-1 mb-3">
      <li class="flex items-center gap-2">
        <Icon icon="ph:clock" class="w-3 h-3" />
        Expiration dates for shared links
      </li>
      <li class="flex items-center gap-2">
        <Icon icon="ph:lock" class="w-3 h-3" />
        Password protection
      </li>
      <li class="flex items-center gap-2">
        <Icon icon="ph:users" class="w-3 h-3" />
        Private group sharing
      </li>
    </ul>
    <a
      href="https://github.com/{GITHUB.REPO_OWNER}/{GITHUB.REPO_NAME}/issues/7"
      target="_blank"
      rel="noopener noreferrer"
      class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors"
    >
      <Icon icon="ph:heart" class="w-4 h-4" />
      Support this feature request
      <Icon icon="ph:arrow-square-out" class="w-3 h-3" />
    </a>
  </InfoCard>
</div>
