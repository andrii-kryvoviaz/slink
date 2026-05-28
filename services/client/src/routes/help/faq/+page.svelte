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
            >* These formats are automatically converted to JPEG on upload.</span
          >
        </p>
      {/snippet}

      {#snippet visibilityContent()}
        <div class="space-y-6">
          <p class="text-slate-700 dark:text-slate-300">
            Every image you upload has a visibility setting. You can toggle it
            from the upload screen or later on the image itself.
          </p>

          <InfoCard variant="neutral" title="Visibility Options">
            <div class="space-y-3">
              <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">
                  <Icon
                    icon="lucide:globe"
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
                    Shown on the
                    <a
                      href="/explore"
                      target="_blank"
                      class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 underline underline-offset-2 font-medium transition-colors"
                    >
                      explore page
                    </a>. Anyone with the direct link can open it.
                  </p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">
                  <Icon
                    icon="lucide:lock"
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
                    Hidden from the explore page. The direct link only works for
                    you, unless you share it with a published link.
                  </p>
                </div>
              </div>
            </div>
          </InfoCard>
        </div>
      {/snippet}

      {#snippet shareContent()}
        <div class="space-y-6">
          <p class="text-slate-700 dark:text-slate-300">
            Sharing creates a separate link on top of your image or collection.
            The original stays untouched, and you can revoke access at any time.
            Manage every link from the
            <a
              href="/shares"
              class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 underline underline-offset-2 font-medium transition-colors"
            >
              Publications
            </a>
            page.
          </p>

          <InfoCard variant="neutral" title="What you can do">
            <ul class="space-y-2">
              <li class="flex items-start gap-2">
                <Icon
                  icon="ph:paper-plane-tilt-duotone"
                  class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5"
                />
                <span>Share a single image or a whole collection</span>
              </li>
              <li class="flex items-start gap-2">
                <Icon
                  icon="ph:lock-simple"
                  class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5"
                />
                <span>Protect the link with a password</span>
              </li>
              <li class="flex items-start gap-2">
                <Icon
                  icon="ph:clock"
                  class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5"
                />
                <span>Set an expiration date</span>
              </li>
              <li class="flex items-start gap-2">
                <Icon
                  icon="lucide:globe"
                  class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5"
                />
                <span
                  >Optionally list the share on the explore page so others can
                  discover it</span
                >
              </li>
              <li class="flex items-start gap-2">
                <Icon
                  icon="ph:eye-slash"
                  class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5"
                />
                <span
                  >Unpublish at any time to revoke access, without changing the
                  original image</span
                >
              </li>
            </ul>
          </InfoCard>

          <InfoCard
            variant="info"
            icon="ph:stack-duotone"
            title="One image, many shares"
          >
            <p>
              Each time you tweak the image parameters like size, format, or
              filters, a new share link is created. That way a single image can
              have multiple share links, one per variation, and you can manage
              them independently. Copying a link publishes it automatically, so
              it's ready to send.
            </p>
          </InfoCard>

          <InfoCard
            variant="info"
            icon="ph:folder-simple-duotone"
            title="Collections are share-only"
          >
            <p>
              Collections don't have a public or private setting of their own.
              Publishing a share link is the only way to show a collection to
              someone else.
            </p>
          </InfoCard>
        </div>
      {/snippet}

      {#snippet publicVsSharedContent()}
        <div class="space-y-6">
          <InfoCard variant="neutral" title="Side by side">
            <div class="space-y-3">
              <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">
                  <Icon
                    icon="lucide:globe"
                    class="w-5 h-5 text-emerald-600 dark:text-emerald-400"
                  />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <span
                      class="text-sm font-semibold text-slate-900 dark:text-slate-100"
                      >Public image</span
                    >
                  </div>
                  <p class="text-sm text-slate-600 dark:text-slate-400">
                    Open access on the image itself. It appears on the explore
                    page, and the direct URL works for anyone. Use it when you
                    want the image out in the open.
                  </p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">
                  <Icon
                    icon="ph:paper-plane-tilt-duotone"
                    class="w-5 h-5 text-indigo-600 dark:text-indigo-400"
                  />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <span
                      class="text-sm font-semibold text-slate-900 dark:text-slate-100"
                      >Shared link</span
                    >
                  </div>
                  <p class="text-sm text-slate-600 dark:text-slate-400">
                    A separate, controlled link generated on top of any image,
                    public or private. Add a password, set an expiration, or
                    revoke it at any time without affecting the image. Use it
                    when you want to hand someone a specific link you can pull
                    back later.
                  </p>
                </div>
              </div>
            </div>
          </InfoCard>

          <InfoCard
            variant="info"
            icon="ph:info"
            title="A private image can still be shared"
          >
            <p>
              Publishing a share link for a private image lets the recipients
              open it through that link only. The image stays hidden everywhere
              else.
            </p>
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
              <div class="flex items-center gap-2">Before Reporting</div>
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
        'Who can see my images?',
        visibilityContent,
      )}
      {@render faqItem(
        'share-feature',
        'How do I share an image or collection?',
        shareContent,
      )}
      {@render faqItem(
        'public-vs-shared',
        "What's the difference between making an image public and sharing it?",
        publicVsSharedContent,
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
