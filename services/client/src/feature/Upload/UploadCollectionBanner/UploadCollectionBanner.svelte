<script lang="ts">
  import type { ShareState } from '@slink/feature/Share';
  import { CopyContainer } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';
  import { Root as BaseInput } from '@slink/ui/components/input';
  import {
    InputGroup,
    InputGroupField,
  } from '@slink/ui/components/input-group';
  import { PrefersReducedMotion } from '@slink/ui/hooks/prefers-reduced-motion.svelte';

  import { plural } from '$lib/utils/i18n';
  import { className as cn } from '$lib/utils/ui/className';
  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { fade, fly } from 'svelte/transition';

  interface CreatedCollection {
    id: string;
    name: string;
  }

  interface Props {
    count: number;
    created?: CreatedCollection | null;
    pending?: boolean;
    share?: ShareState | null;
    onCreate?: (name: string) => void;
    onView?: () => void;
    class?: string;
  }

  let {
    count,
    created = null,
    pending = false,
    share = null,
    onCreate,
    onView,
    class: className,
  }: Props = $props();

  let name = $state('');
  let dismissed = $state(false);

  const reducedMotion = new PrefersReducedMotion();

  type BannerStep = 'choose' | 'created' | 'published';

  const step = $derived.by<BannerStep>(() => {
    if (!created) {
      return 'choose';
    }

    if (share?.isInitialized) {
      return 'published';
    }

    return 'created';
  });

  const flyParams = $derived(
    reducedMotion.current
      ? { duration: 0, y: 0 }
      : { duration: 240, y: 6, easing: cubicOut },
  );

  const fadeParams = $derived(
    reducedMotion.current ? { duration: 0 } : { duration: 200 },
  );

  const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !pending) {
      onCreate?.(name);
    }
  };
</script>

{#if !dismissed}
  <div
    class={cn(
      'rounded-xl bg-white/80 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700/50 shadow-sm backdrop-blur-sm p-5 space-y-4',
      className,
    )}
  >
    <div class="flex items-start gap-4">
      <div
        class="w-10 h-10 rounded-xl bg-slate-100/70 dark:bg-slate-700/40 flex items-center justify-center shrink-0 border border-slate-200/50 dark:border-slate-600/30"
      >
        {#if step === 'published'}
          <Icon
            icon="ph:link-simple"
            class="w-5 h-5 text-slate-600 dark:text-slate-400"
          />
        {:else}
          <Icon
            icon="ph:folder-simple"
            class="w-5 h-5 text-slate-600 dark:text-slate-400"
          />
        {/if}
      </div>

      <div class="flex-1 min-w-0 space-y-1">
        {#if step === 'choose'}
          <span
            class="block text-base sm:text-lg font-semibold bg-gradient-to-r from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 bg-clip-text text-transparent"
          >
            Combine into a collection
          </span>
          <span
            class="block text-sm leading-snug text-slate-500 dark:text-slate-400"
          >
            {plural(count, [
              'Optionally group this # image into a collection to view and share them as one.',
              'Optionally group these # images into a collection to view and share them as one.',
            ])}
          </span>
        {:else if step === 'created'}
          <span
            class="block text-base sm:text-lg font-semibold bg-gradient-to-r from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 bg-clip-text text-transparent"
          >
            Collection created
          </span>
          <span
            class="block truncate text-sm text-slate-500 dark:text-slate-400"
          >
            {created?.name ?? 'Unnamed'}
          </span>
        {:else}
          <span
            class="block text-base sm:text-lg font-semibold bg-gradient-to-r from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 bg-clip-text text-transparent"
          >
            Ready to share
          </span>
          <span
            class="block text-sm leading-snug text-slate-500 dark:text-slate-400"
          >
            Anyone with this link can view the collection.
          </span>
        {/if}
      </div>

      <button
        type="button"
        class="shrink-0 -mr-1 -mt-1 rounded-md p-1 text-slate-400 transition-colors hover:bg-slate-500/10 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500/30 dark:text-slate-500 dark:hover:bg-slate-400/10 dark:hover:text-slate-300"
        title="Maybe later"
        aria-label="Maybe later"
        onclick={() => (dismissed = true)}
      >
        <Icon icon="ph:x" class="h-4 w-4" />
      </button>
    </div>

    <div class="grid">
      {#key step}
        <div
          class="col-start-1 row-start-1"
          in:fly|local={flyParams}
          out:fade|local={fadeParams}
        >
          {#if step === 'choose'}
            <InputGroup
              size="md"
              fluid
              role="group"
              aria-label="Create a collection from these images"
            >
              <BaseInput
                bind:value={name}
                placeholder="Unnamed"
                disabled={pending}
                onkeydown={handleKeydown}
                class={cn(
                  InputGroupField({ size: 'sm' }),
                  'h-10 min-w-0 flex-1 rounded-none border-0 bg-transparent shadow-none focus-visible:border-transparent focus-visible:ring-0',
                )}
              />

              <div class="shrink-0 pr-2">
                <Button
                  variant="primary"
                  size="xs"
                  rounded="sm"
                  spacing="tight"
                  loading={pending}
                  disabled={pending}
                  onclick={() => onCreate?.(name)}
                >
                  <Icon icon="ph:plus" class="h-3.5 w-3.5" />
                  Create collection
                </Button>
              </div>
            </InputGroup>
          {:else if step === 'created'}
            <div class="flex flex-wrap items-center gap-2">
              <Button
                variant="soft-blue"
                size="sm"
                rounded="full"
                spacing="tight"
                onclick={onView}
              >
                <Icon icon="ph:arrow-square-out" class="h-3.5 w-3.5" />
                View collection
              </Button>
              <Button
                variant="glass"
                size="sm"
                rounded="full"
                spacing="tight"
                loading={share?.isLoading}
                disabled={share?.isLoading}
                onclick={() => share?.load()}
              >
                <Icon icon="ph:link-simple" class="h-3.5 w-3.5" />
                Create publication
              </Button>
            </div>
          {:else}
            <div class="space-y-3">
              <CopyContainer value={share?.shareUrl ?? ''} size="md" fluid />

              <div class="flex flex-wrap items-center gap-2">
                <Button
                  variant="soft-blue"
                  size="sm"
                  rounded="full"
                  spacing="tight"
                  onclick={onView}
                >
                  <Icon icon="ph:arrow-square-out" class="h-3.5 w-3.5" />
                  View collection
                </Button>
                <Button
                  variant="glass"
                  size="sm"
                  rounded="full"
                  spacing="tight"
                  onclick={() => share?.unpublish()}
                >
                  <Icon icon="ph:eye-slash" class="h-3.5 w-3.5" />
                  Unpublish
                </Button>
              </div>
            </div>
          {/if}
        </div>
      {/key}
    </div>
  </div>
{/if}
