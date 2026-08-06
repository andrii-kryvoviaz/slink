<script lang="ts">
  import Gallery from './Gallery.svelte';
  import TokenSwatch from './TokenSwatch.svelte';
  import VariantMatrix from './VariantMatrix.svelte';
  import { tokenGroups } from './contract';
  import { labels } from './labels';

  type Props = { scope: 'light' | 'dark' };

  let { scope }: Props = $props();
</script>

<div
  data-token-scope={scope}
  class="bg-background text-foreground min-w-0 flex-1 p-4 {scope === 'dark'
    ? 'dark'
    : ''}"
  data-testid="token-pane-{scope}"
>
  <h2 class="text-foreground mb-3 text-sm font-semibold">
    {scope === 'dark' ? labels.dark : labels.light}
  </h2>

  <section class="mb-6">
    <h3 class="text-muted-foreground mb-2 text-xs font-semibold uppercase">
      {labels.tokens}
    </h3>
    <div class="flex flex-col gap-4">
      {#each tokenGroups as group (group.name)}
        <div>
          <code class="text-muted-foreground font-mono text-[11px]"
            >{group.name}</code
          >
          <div
            class="mt-1 grid grid-cols-2 gap-2 xl:grid-cols-3 2xl:grid-cols-4"
          >
            {#each group.tokens as token (token)}
              <TokenSwatch {token} />
            {/each}
          </div>
        </div>
      {/each}
    </div>
  </section>

  <section class="mb-6">
    <h3 class="text-muted-foreground mb-2 text-xs font-semibold uppercase">
      {labels.components}
    </h3>
    <Gallery />
  </section>

  <section>
    <h3 class="text-muted-foreground mb-2 text-xs font-semibold uppercase">
      {labels.recipes}
    </h3>
    <VariantMatrix />
  </section>
</div>
