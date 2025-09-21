<script lang="ts">
  import { Version } from '@slink/feature/Settings/Version';

  interface Props {
    className?: string;
  }

  let { className = '' }: Props = $props();
</script>

<footer
  class={`flex items-center justify-end px-4 py-3 bg-transparent flex-shrink-0 ${className}`}
>
  <Version showUpdateIndicator={true} className="text-muted-foreground/80" />
</footer>
