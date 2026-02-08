<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { Shortcut } from '@slink/ui/components/shortcut';

  import { browser } from '$app/environment';
  import { page } from '$app/state';
  import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { usePostViewerState } from '@slink/lib/state/PostViewerState.svelte';

  import PostViewerItem from './PostViewerItem.svelte';
  import PostViewerNavigation from './PostViewerNavigation.svelte';

  const viewerState = usePostViewerState();
  const currentUser = $derived(page.data.user ?? null);

  const animatingState = useAutoReset(500);
  let lastWheelTime = $state(0);
  const WHEEL_THRESHOLD = 50;
  const WHEEL_COOLDOWN = 600;

  $effect(() => {
    if (browser && viewerState.isOpen) {
      document.body.style.overflow = 'hidden';
    } else if (browser) {
      document.body.style.overflow = '';
    }
  });

  function handleClose() {
    viewerState.clearUrlParam();
    viewerState.close();
  }

  function navigateNext() {
    if (viewerState.hasNext && !animatingState.active) {
      animatingState.trigger();
      viewerState.next();
    }
  }

  function navigatePrev() {
    if (viewerState.hasPrev && !animatingState.active) {
      animatingState.trigger();
      viewerState.prev();
    }
  }

  function handleWheel(e: WheelEvent) {
    e.preventDefault();

    const now = Date.now();
    if (animatingState.active || now - lastWheelTime < WHEEL_COOLDOWN) return;

    if (Math.abs(e.deltaY) > WHEEL_THRESHOLD) {
      lastWheelTime = now;
      if (e.deltaY > 0) {
        navigateNext();
      } else {
        navigatePrev();
      }
    }
  }

  let touchStartY = $state(0);
  const TOUCH_THRESHOLD = 50;

  function handleTouchStart(e: TouchEvent) {
    touchStartY = e.touches[0].clientY;
  }

  function handleTouchEnd(e: TouchEvent) {
    if (animatingState.active) return;

    const touchEndY = e.changedTouches[0].clientY;
    const deltaY = touchStartY - touchEndY;

    if (deltaY > TOUCH_THRESHOLD) {
      navigateNext();
    } else if (deltaY < -TOUCH_THRESHOLD) {
      navigatePrev();
    }
  }

  function handleBackdropClick(e: MouseEvent) {
    if (e.target === e.currentTarget) {
      handleClose();
    }
  }

  let translateY = $derived(
    viewerState.isStandaloneMode ? 0 : -viewerState.currentIndex * 100,
  );
</script>

{#if viewerState.isOpen}
  <div
    class="fixed inset-0 z-50 bg-black overflow-hidden"
    transition:fade={{ duration: 200 }}
    role="dialog"
    aria-modal="true"
    tabindex="-1"
    onclick={handleBackdropClick}
    onkeydown={() => {}}
    onwheel={handleWheel}
    ontouchstart={handleTouchStart}
    ontouchend={handleTouchEnd}
  >
    <Button
      variant="glass-dark"
      size="icon"
      rounded="full"
      motion="subtle"
      onclick={handleClose}
      class="absolute top-4 right-4 z-10"
      aria-label="Close viewer"
    >
      <Icon icon="heroicons:x-mark" class="w-5 h-5" />
    </Button>

    {#if !viewerState.isStandaloneMode}
      <PostViewerNavigation
        onPrev={navigatePrev}
        onNext={navigateNext}
        hasPrev={viewerState.hasPrev}
        hasNext={viewerState.hasNext}
        class="absolute right-4 top-1/2 -translate-y-1/2 z-10 hidden md:flex"
      />
    {/if}

    <div
      class="h-full w-full transition-transform duration-500 ease-out"
      style="transform: translateY({translateY}%);"
    >
      {#if viewerState.isStandaloneMode && viewerState.currentItem}
        <PostViewerItem
          image={viewerState.currentItem}
          {currentUser}
          isActive={true}
          onClose={handleClose}
        />
      {:else}
        {#each viewerState.items as image, index (image.id)}
          <PostViewerItem
            {image}
            {currentUser}
            isActive={index === viewerState.currentIndex}
            onClose={handleClose}
          />
        {/each}
      {/if}
    </div>

    <Shortcut
      key="Escape"
      onHit={handleClose}
      enabled={viewerState.isOpen}
      hidden
    />
    <Shortcut
      key="ArrowUp"
      onHit={navigatePrev}
      enabled={viewerState.isOpen}
      hidden
    />
    <Shortcut
      key="ArrowLeft"
      onHit={navigatePrev}
      enabled={viewerState.isOpen}
      hidden
    />
    <Shortcut
      key="ArrowDown"
      onHit={navigateNext}
      enabled={viewerState.isOpen}
      hidden
    />
    <Shortcut
      key="ArrowRight"
      onHit={navigateNext}
      enabled={viewerState.isOpen}
      hidden
    />
  </div>
{/if}
