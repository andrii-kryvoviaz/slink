<script lang="ts">
  interface Props {
    theme: string;
  }

  let { theme }: Props = $props();

  const colors = [
    '--color-background',
    '--color-primary',
    '--color-accent',
    '--color-info',
    '--color-foreground',
  ];
</script>

<span class="flex w-full items-center gap-1" data-theme={theme}>
  {#each colors as color (color)}
    <span
      class="h-5 flex-1 rounded-md"
      style:background-color="var({color})"
      style:border="1px solid oklch(from var(--color-foreground) l c h / 0.25)"
    ></span>
  {/each}
</span>
