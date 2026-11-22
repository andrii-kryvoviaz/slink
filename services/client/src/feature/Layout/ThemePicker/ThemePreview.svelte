<script lang="ts">
  interface Props {
    theme?: string;
    /** Inline variables, for a theme that is not saved yet. */
    variables?: string;
  }

  let { theme, variables }: Props = $props();

  const colors = [
    '--color-background',
    '--color-primary',
    '--color-accent',
    '--color-info',
    '--color-foreground',
  ];
</script>

<span
  data-slot="theme-preview"
  class="flex w-full items-center gap-1"
  data-theme={theme}
  style={variables}
>
  {#each colors as color (color)}
    <span
      class="h-5 flex-1 rounded-md"
      style:background-color="var({color})"
      style:border="1px solid oklch(from var(--color-foreground) l c h / 0.25)"
    ></span>
  {/each}
</span>
