<script lang="ts">
  import { InfoCard } from '@slink/feature/Feedback/InfoCard';
  import { Subtitle, Title } from '@slink/feature/Text';
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';
  import { type Snippet, onMount } from 'svelte';

  import { browser } from '$app/environment';
  import { page } from '$app/state';
  import { GITHUB } from '$lib/constants/app';
  import Icon from '@iconify/svelte';
  import { fade, slide } from 'svelte/transition';

  const imageFormats = [
    'image/bmp',
    'image/png',
    'image/jpeg',
    'image/jpg',
    'image/gif',
    'image/webp',
    'image/svg+xml',
    'image/svg',
    'image/x-icon',
    'image/vnd.microsoft.icon',
    'image/x-tga',
    'image/avif',
  ];

  const convertedFormats = ['image/heic *', 'image/tiff *', 'image/tif *'];

  let openedQuestion: string | undefined = $state();

  const toggleQuestion = (slug: string) => {
    openedQuestion = openedQuestion === slug ? undefined : slug;

    if (browser && openedQuestion) {
      window.location.hash = openedQuestion;
    } else if (browser) {
      history.replaceState(null, '', window.location.pathname);
    }
  };

  const scrollToQuestion = (hash: string) => {
    if (!browser) return;
    setTimeout(() => {
      document.getElementById(hash)?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
      });
    }, 30);
  };

  onMount(() => {
    const slug = page.url.hash?.slice(1);
    if (!slug) return;

    if (document.getElementById(slug)) {
      openedQuestion = slug;
      scrollToQuestion(slug);
    }
  });
</script>

{#snippet faqItem(slug: string, title: string, content: Snippet)}
  <div
    id={slug}
    class="bg-white dark:bg-slate-800/50 rounded-2xl border border-slate-200/50 dark:border-slate-700/50 shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md"
  >
    <button
      class="w-full px-6 py-5 text-left focus:outline-none transition-all duration-200 hover:bg-slate-50 dark:hover:bg-slate-700/30"
      onclick={() => toggleQuestion(slug)}
      aria-expanded={openedQuestion === slug}
      aria-controls={`answer-${slug}`}
    >
      <div class="flex items-center justify-between">
        <h3
          class="text-lg font-semibold text-slate-900 dark:text-slate-100 pr-4"
        >
          {title}
        </h3>
        <div class="flex-shrink-0">
          <div
            class="transform transition-transform duration-200 {openedQuestion ===
            slug
              ? 'rotate-45'
              : ''}"
          >
            <Icon
              icon="ph:plus"
              class="h-5 w-5 text-slate-500 dark:text-slate-400"
            />
          </div>
        </div>
      </div>
    </button>

    {#if openedQuestion === slug}
      <div
        id={`answer-${slug}`}
        class="px-6 pb-6"
        transition:slide={{ duration: 300 }}
      >
        <div class="pt-2 border-t border-slate-100 dark:border-slate-700">
          <div
            class="prose prose-slate dark:prose-invert max-w-none text-slate-600 dark:text-slate-300"
          >
            {@render content()}
          </div>
        </div>
      </div>
    {/if}
  </div>
{/snippet}

<svelte:head>
  <title>FAQ | Slink</title>
  <meta
    name="description"
    content="Frequently asked questions about Slink - image sharing platform"
  />
</svelte:head>

<div class="min-h-full">
  <div
    class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8"
    in:fade={{ duration: 400 }}
  >
    <div class="text-center mb-12">
      <Title size="xl" weight="bold" class="mb-4"
        >Frequently Asked Questions</Title
      >
      <Subtitle size="lg" class="max-w-2xl mx-auto">
        Find answers to common questions about using Slink for image sharing and
        management
      </Subtitle>
    </div>

    <div class="space-y-4">
      {#snippet formatsContent()}
        <p>Slink supports the following mime types:</p>

        <div class="mt-2 w-full flex flex-wrap gap-2">
          {#each imageFormats as format}
            <Badge variant="blue" outline>
              {format}
            </Badge>
          {/each}
          {#each convertedFormats as format}
            <Badge variant="amber" outline>
              {format}
            </Badge>
          {/each}
        </div>

        <p class="mt-4 text-xs">
          <span class="font-light"
            >* Source images are forced to be converted to JPEG format.</span
          >
        </p>
      {/snippet}

      {#snippet visibilityContent()}
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
                    Images are shared in the public gallery and visible to all
                    users
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
                    Images are truly private and only accessible by the owner.
                    To share a private image, use the copy button to create a
                    shared link
                  </p>
                </div>
              </div>
            </div>
          </InfoCard>

          <InfoCard variant="info" icon="ph:info" title="Important Note">
            <p>
              Images are private by default and only accessible to the
              authenticated owner. To share an image with others, use the copy
              button which creates a shared link.
            </p>
          </InfoCard>
        </div>
      {/snippet}

      {#snippet shareContent()}
        <div class="space-y-6">
          <p>
            You can easily share your images with others using the direct link
            provided for each uploaded image.
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
              I'm working on advanced sharing features that will give you more
              control over your image sharing:
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
      {/snippet}

      {#snippet issueContent()}
        <div class="space-y-6">
          <p class="text-slate-700 dark:text-slate-300">
            I appreciate your help in making Slink better! If you've encountered
            a bug or have suggestions for improvement, here's how you can help:
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
                  >Gather relevant details (browser, steps to reproduce,
                  screenshots)</span
                >
              </li>
            </ul>
          </InfoCard>

          <InfoCard
            variant="info"
            icon="ph:github-logo"
            title="Report on GitHub"
          >
            <p class="mb-3">
              GitHub Issues is my primary platform for tracking bugs and feature
              requests. Your reports help me prioritize improvements.
            </p>
            <a
              href="https://github.com/{GITHUB.REPO_OWNER}/{GITHUB.REPO_NAME}/issues/new"
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
              Every report helps me improve Slink for the entire community. I
              review all issues and prioritize fixes based on impact and
              frequency.
            </p>
          </InfoCard>
        </div>
      {/snippet}

      {@render faqItem(
        'supported-image-formats',
        'What image formats Slink supports?',
        formatsContent,
      )}
      {@render faqItem(
        'image-visibility',
        'What is the visibility of my images?',
        visibilityContent,
      )}
      {@render faqItem(
        'share-feature',
        'Can I share my images with others?',
        shareContent,
      )}
      {@render faqItem(
        'found-an-issue',
        'I found an issue, how can I report it?',
        issueContent,
      )}
    </div>

    <div class="mt-16 text-center">
      <div
        class="bg-white dark:bg-slate-800/50 rounded-2xl border border-slate-200/50 dark:border-slate-700/50 p-8"
      >
        <Icon
          icon="ph:chat-circle-text"
          class="h-12 w-12 text-slate-400 mx-auto mb-4"
        />
        <h3
          class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-2"
        >
          Still have questions?
        </h3>
        <p class="text-slate-600 dark:text-slate-400 mb-6">
          Can't find the answer you're looking for? Please get in touch with us
          for further assistance.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <a
            href="https://github.com/{GITHUB.REPO_OWNER}/{GITHUB.REPO_NAME}/issues"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center justify-center px-6 py-3 bg-slate-900 hover:bg-slate-800 dark:bg-slate-100 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-medium rounded-xl transition-colors duration-200"
          >
            <Icon icon="ph:github-logo" class="h-5 w-5 mr-2" />
            Report an Issue
          </a>
          <a
            href="https://docs.slinkapp.io"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center justify-center px-6 py-3 bg-white hover:bg-slate-50 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-900 dark:text-slate-100 font-medium rounded-xl border border-slate-200 dark:border-slate-600 transition-colors duration-200"
          >
            <Icon icon="ph:book-open" class="h-5 w-5 mr-2" />
            View Documentation
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
