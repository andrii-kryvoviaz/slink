<script lang="ts">
  type Props = { token: string };

  let { token }: Props = $props();

  let swatch = $state<HTMLElement | null>(null);
  let value = $state('');

  const TRANSPARENT = 'rgba(0, 0, 0, 0)';

  $effect(() => {
    if (!swatch) return;

    const computed = getComputedStyle(swatch);
    const color = computed.backgroundColor;
    const declared = computed.getPropertyValue(token).trim();

    value = color && color !== TRANSPARENT ? color : declared;
  });
</script>

<div
  class="border-border/60 bg-card/40 flex min-w-0 items-center gap-3 rounded-md border p-2"
  data-token={token}
>
  <span
    bind:this={swatch}
    class="border-border/60 size-10 shrink-0 border"
    style="background-color: var({token}); box-shadow: var({token}); border-radius: var({token});"
  ></span>
  <span class="flex min-w-0 flex-col">
    <code class="text-foreground truncate font-mono text-[11px]">{token}</code>
    <code class="text-muted-foreground truncate font-mono text-[10px]"
      >{value}</code
    >
  </span>
</div>
