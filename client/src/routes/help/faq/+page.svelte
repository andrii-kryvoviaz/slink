<script lang="ts">
  import { faqQuestions } from '@slink/routes/help/faq/faq.questions';
  import { onMount } from 'svelte';

  import { browser } from '$app/environment';
  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';
  import { fade, slide } from 'svelte/transition';

  let openedQuestion: string | undefined = faqQuestions.at(0)?.slug;

  const openQuestion = (slug: string) => {
    openedQuestion = slug;

    if (browser) window.location.hash = slug;
  };

  const scrollToQuestion = (hash: string) => {
    if (!browser) return;

    document.getElementById(hash)?.scrollIntoView({ behavior: 'smooth' });
  };

  onMount(() => {
    // Open and scroll to the question if the hash is present
    const hash = $page.url.hash;
    const slug = hash?.slice(1);

    if (!slug || openedQuestion == slug) return;

    const question = faqQuestions.find((q) => q.slug === slug);

    if (question) {
      openQuestion(slug);
      scrollToQuestion(hash);
    }
  });
</script>

<svelte:head>
  <title>FAQ | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div class="container mx-auto flex flex-col px-6 py-6 sm:py-10">
    <div>
      <h1
        class="text-center text-2xl font-semibold capitalize text-gray-800 dark:text-white lg:text-3xl"
      >
        FAQ
      </h1>

      <div class="mx-auto mt-6 flex justify-center">
        <span class="inline-block h-1 w-40 rounded-full bg-indigo-500" />
        <span class="mx-1 inline-block h-1 w-3 rounded-full bg-indigo-500" />
        <span class="inline-block h-1 w-1 rounded-full bg-indigo-500" />
      </div>
    </div>

    <div class="mt-8">
      {#each faqQuestions as { title, content, slug }, i}
        <div id={slug}>
          <button
            class="flex w-full items-center focus:outline-none"
            on:click={() => openQuestion(slug)}
          >
            {#if openedQuestion === slug}
              <Icon
                icon="ep:minus"
                class="h-6 w-6 flex-shrink-0 text-blue-500"
              />
            {:else}
              <Icon
                icon="ep:plus"
                class="h-6 w-6 flex-shrink-0 text-blue-500"
              />
            {/if}

            <h1 class="mx-4 text-xl text-gray-700 dark:text-white">{title}</h1>
          </button>

          {#if openedQuestion === slug}
            <div out:slide={{ duration: 200 }} class="mt-8">
              <div class="max-w-3xl px-4 text-gray-500 dark:text-gray-300">
                {@html content}
              </div>
            </div>
          {/if}
        </div>

        {#if i !== faqQuestions.length - 1}
          <hr class="my-8 border-gray-200 dark:border-gray-700" />
        {/if}
      {/each}
    </div>
  </div>
</section>
