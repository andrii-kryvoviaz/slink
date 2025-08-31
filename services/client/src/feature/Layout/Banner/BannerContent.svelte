<script lang="ts">
  interface Props {
    title: string;
    description: string;
  }

  let { title, description }: Props = $props();
</script>

<div>
  <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
    {title}
  </h3>
  <p class="text-xs text-slate-600 dark:text-slate-400">
    {description}
  </p>
</div>
