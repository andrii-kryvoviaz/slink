<script lang="ts">
  import { faqQuestions } from '@slink/routes/help/faq/faq.questions';
  import { onMount } from 'svelte';

  import { browser } from '$app/environment';
  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';
  import { fade, slide } from 'svelte/transition';

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
    }, 100);
  };

  onMount(() => {
    const hash = $page.url.hash;
    const slug = hash?.slice(1);

    if (!slug) return;

    const question = faqQuestions.find((q) => q.slug === slug);

    if (question) {
      openedQuestion = slug;
      scrollToQuestion(slug);
    }
  });
</script>

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
      <h1 class="text-4xl font-bold text-slate-900 dark:text-slate-100 mb-4">
        Frequently Asked Questions
      </h1>
      <p class="text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
        Find answers to common questions about using Slink for image sharing and
        management
      </p>
    </div>

    <div class="space-y-4">
      {#each faqQuestions as { title, content, slug }, i}
        {@const ContentComponent = content}
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
                  <ContentComponent />
                </div>
              </div>
            </div>
          {/if}
        </div>
      {/each}
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
            href="https://github.com/andrii-kryvoviaz/slink/issues"
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
