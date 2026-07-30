<script lang="ts">
  import { customization } from '@slink/lib/settings';

  interface Props {
    class?: string;
  }

  let { class: className }: Props = $props();

  const handleError = (event: Event) => {
    (event.currentTarget as HTMLImageElement).src = '/favicon.png';
  };
</script>

<img
  class={className}
  src={customization.logoUrl}
  alt={customization.siteName}
  onerror={handleError}
/>
