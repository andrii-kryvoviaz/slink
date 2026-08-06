<script lang="ts">
  import { page } from '$app/state';

  import Pane from './Pane.svelte';
  import { scopedTokenCss } from './contract';
  import { labels } from './labels';

  const MODES = ['split', 'light', 'dark'] as const;

  type Mode = (typeof MODES)[number];

  const modeLabels: Record<Mode, string> = {
    split: labels.split,
    light: labels.light,
    dark: labels.dark,
  };

  const isMode = (value: string | null): value is Mode =>
    MODES.includes(value as Mode);

  let mode = $derived.by(() => {
    const requested = page.url.searchParams.get('mode');
    if (isMode(requested)) return requested;
    return 'split';
  });

  $effect(() => {
    const root = document.documentElement;
    const wasDark = root.classList.contains('dark');

    root.classList.remove('dark');

    return () => {
      if (wasDark) root.classList.add('dark');
    };
  });
</script>

<svelte:head>
  {@html `<style>${scopedTokenCss}</style>`}
</svelte:head>

<div class="p-4" data-testid="token-preview">
  <div class="mb-4 flex items-center gap-3">
    <h1 class="text-sm font-semibold">{labels.title}</h1>
    <nav class="flex gap-2">
      {#each MODES as option (option)}
        <a
          class="border-border/60 rounded-md border px-2 py-1 text-xs"
          class:font-semibold={mode === option}
          href="?mode={option}"
          data-testid="token-mode-{option}"
        >
          {modeLabels[option]}
        </a>
      {/each}
    </nav>
  </div>

  <div class="flex flex-col gap-4 xl:flex-row" data-testid="token-panes">
    {#if mode !== 'dark'}
      <Pane scope="light" />
    {/if}
    {#if mode !== 'light'}
      <Pane scope="dark" />
    {/if}
  </div>
</div>
