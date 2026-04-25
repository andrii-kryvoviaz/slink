<script lang="ts">
  import {
    TooltipContent,
    TooltipProvider,
    TooltipRoot,
    TooltipTrigger,
  } from '@slink/ui/components/tooltip';

  import Icon from '@iconify/svelte';

  import type { ViewMode } from '@slink/lib/settings';

  import { className } from '@slink/utils/ui/className';
  import { getNextRovingIndex } from '@slink/utils/ui/rovingFocus';

  import { viewModeSliderTheme } from './ViewModeToggle.theme';
  import type { ViewModeToggleProps } from './ViewModeToggle.types.svelte';
  import { viewModeRegistry } from './ViewModeToggle.types.svelte';

  interface Props extends ViewModeToggleProps {}

  let {
    value,
    modes,
    size = 'md',
    className: customClassName,
    disabled = false,
    on,
  }: Props = $props();

  const styles = $derived(viewModeSliderTheme({ size }));

  const activeIndex = $derived(Math.max(0, modes.indexOf(value)));
  const stepCount = $derived(modes.length);

  const select = (mode: ViewMode) => {
    if (disabled || mode === value) return;
    on?.change(mode);
  };

  const handleKeydown = (event: KeyboardEvent) => {
    if (disabled) {
      return;
    }

    const nextIndex = getNextRovingIndex(event.key, activeIndex, stepCount);
    if (nextIndex === null) {
      return;
    }

    event.preventDefault();
    select(modes[nextIndex]);
  };
</script>

<TooltipProvider delayDuration={250} disableHoverableContent>
  <div
    class={className(
      styles.root(),
      disabled && styles.disabled(),
      customClassName,
    )}
    role="radiogroup"
    aria-label="View mode"
    aria-disabled={disabled}
    tabindex={-1}
    onkeydown={handleKeydown}
  >
    <div class={styles.track()}>
      <div
        class={styles.thumb()}
        style:width="calc((100% - 0.25rem) / {stepCount})"
        style:transform="translateX({activeIndex * 100}%)"
        aria-hidden="true"
      ></div>

      {#each modes as mode, index (mode)}
        {@const isActive = index === activeIndex}
        {@const config = viewModeRegistry[mode]}

        <TooltipRoot>
          <TooltipTrigger>
            {#snippet child({ props })}
              <button
                {...props}
                type="button"
                role="radio"
                aria-checked={isActive}
                aria-label={config.label}
                tabindex={isActive ? 0 : -1}
                {disabled}
                onclick={() => select(mode)}
                class={styles.step()}
              >
                {#if isActive}
                  <Icon icon={config.icon} class={styles.icon()} />
                {:else}
                  <span class={styles.dot()} aria-hidden="true"></span>
                {/if}
              </button>
            {/snippet}
          </TooltipTrigger>
          <TooltipContent side="bottom" sideOffset={8} variant="default">
            {config.label}
          </TooltipContent>
        </TooltipRoot>
      {/each}
    </div>
  </div>
</TooltipProvider>
