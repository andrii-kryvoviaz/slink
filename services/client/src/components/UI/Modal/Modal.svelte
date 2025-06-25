<script lang="ts">
  import type { Snippet } from 'svelte';
  import { twMerge } from 'tailwind-merge';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { Button, type ButtonVariant } from '@slink/components/UI/Action';
  import { Loader } from '@slink/components/UI/Loader';

  interface Props {
    open?: boolean;
    loading?: boolean;
    align?: 'top' | 'middle' | 'bottom';
    variant?: ButtonVariant;
    icon?: Snippet;
    title?: Snippet;
    extra?: Snippet;
    content?: Snippet;
    confirm?: Snippet;
    on: {
      confirm: () => void;
    };
  }

  let {
    open = true,
    loading = false,
    align = 'top',
    variant,
    icon,
    title,
    extra,
    content,
    confirm,
    on,
  }: Props = $props();

  let innerModal: HTMLElement | null = $state(null);

  const closeModal = (e?: MouseEvent) => {
    // Prevent closing the modal when clicking inside of innerModal
    if (e && innerModal?.contains(e.target as Node)) return;

    open = false;
  };

  const confirmButtonDefaultClasses =
    'mt-2 w-full transform rounded-md px-4 py-2 text-sm font-medium capitalize tracking-wide transition-colors duration-300 focus:outline-hidden focus:ring-3 focus:ring-opacity-40 sm:mt-0 sm:w-auto';

  const confirmButtonDefaultAccentClasses =
    'bg-blue-600 text-white hover:bg-blue-500 focus:ring-blue-300';

  const innerModalDefaultClasses =
    'relative inline-block transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all rtl:text-right dark:bg-gray-900 sm:my-8 sm:w-full sm:max-w-sm sm:p-6 align-middle';

  const modalContainerDefaultClasses =
    'flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:p-0';

  const getAlignClasses = () => {
    switch (align) {
      case 'top':
        return 'items-start';
      case 'bottom':
        return 'items-end';
      default:
        return 'items-center';
    }
  };

  let confirmButtonClasses = $derived(
    !variant
      ? `${confirmButtonDefaultClasses} ${confirmButtonDefaultAccentClasses}`
      : confirmButtonDefaultClasses,
  );

  let modalContainerClasses = $derived(
    twMerge(modalContainerDefaultClasses, getAlignClasses()),
  );
</script>

<svelte:window on:keydown|window={(e) => e.key === 'Escape' && closeModal()} />

{#if open}
  <div
    in:fade
    class="fixed inset-0 z-40 overflow-y-auto bg-black bg-opacity-50 backdrop-blur-xs"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    onclick={closeModal}
  >
    <div class={modalContainerClasses}>
      <span
        class="hidden sm:inline-block sm:h-screen sm:align-middle"
        aria-hidden="true">&#8203;</span
      >

      <div bind:this={innerModal} class={innerModalDefaultClasses}>
        <div>
          <div class="flex items-center justify-center">
            {@render icon?.()}
          </div>

          <div class="mt-2 text-center">
            <h3
              class="text-lg font-medium capitalize leading-6 text-gray-800 dark:text-white"
              id="modal-title"
            >
              {@render title?.()}
            </h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              {#if content}
                {@render content?.()}
              {:else}
                Are you sure?
              {/if}
            </p>
          </div>
        </div>

        <div class="mt-5 sm:flex sm:items-end sm:justify-between">
          {#if extra}
            {@render extra?.()}
          {:else}
            &nbsp;
          {/if}

          <div class="sm:flex sm:items-center">
            <Button
              class="mt-2 w-full transform rounded-md border border-gray-200 px-4 py-2 text-sm font-medium capitalize tracking-wide text-gray-700 transition-colors duration-300 hover:bg-gray-100 focus:outline-hidden focus:ring-3 focus:ring-gray-300 focus:ring-opacity-40 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800 sm:mx-2 sm:mt-0 sm:w-auto"
              onclick={closeModal}
            >
              Cancel
            </Button>

            <Button
              {variant}
              class={confirmButtonClasses}
              disabled={loading}
              onclick={on?.confirm}
            >
              {#if loading}
                {#snippet loadingIcon()}
                  <Loader
                    variant="simple"
                    size="sm"
                    class="mr-4 !border-white/50 !border-t-white"
                  />
                {/snippet}
              {/if}

              {#if confirm}
                {@render confirm?.()}
              {:else}
                Confirm
              {/if}
            </Button>
          </div>
        </div>
      </div>
    </div>
  </div>
{/if}
