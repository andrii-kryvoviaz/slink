<script lang="ts">
  import type { Snippet } from 'svelte';

  import { AccentIcon } from '../AccentIcon';
  import { setShareControls } from '../State/Context';
  import type { ShareState } from '../State/State.svelte';
  import { panel } from './Panel.theme';

  interface Props {
    state: ShareState;
    variant?: 'card' | 'plain';
    icon?: Snippet;
    title?: Snippet;
    description?: Snippet;
    options?: Snippet;
    body: Snippet;
    footer?: Snippet;
  }

  let {
    state,
    variant = 'card',
    icon,
    title,
    description,
    options,
    body,
    footer,
  }: Props = $props();

  setShareControls(state);

  const hasHeader = $derived(
    Boolean(icon) || Boolean(title) || Boolean(description) || Boolean(options),
  );

  const theme = $derived(panel({ variant }));
</script>

<div class={theme.root()}>
  {#if hasHeader}
    <div class={theme.header()}>
      <div class={theme.titleBlock()}>
        {#if icon}
          <AccentIcon size="md">
            {@render icon()}
          </AccentIcon>
        {/if}

        {#if title || description}
          <div class={theme.textBlock()}>
            {#if title}
              <h3 class={theme.titleRow()}>
                {@render title()}
              </h3>
            {/if}
            {#if description}
              <p class={theme.description()}>
                {@render description()}
              </p>
            {/if}
          </div>
        {/if}
      </div>

      {#if options}
        <div class={theme.optionsSlot()}>
          {@render options()}
        </div>
      {/if}
    </div>
  {/if}

  <div class={theme.body()}>
    {@render body()}
  </div>

  {#if footer}
    <div class={theme.footer()}>
      {@render footer()}
    </div>
  {/if}
</div>
