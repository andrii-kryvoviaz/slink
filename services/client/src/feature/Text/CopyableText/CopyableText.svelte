<script lang="ts">
  import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
  import { copyText } from '$lib/utils/ui/clipboard';
  import Icon from '@iconify/svelte';

  interface Props {
    text: string | null;
    class?: string;
  }

  let { text, class: className = '' }: Props = $props();
  const copiedState = useAutoReset(2000);

  async function handleCopy() {
    if (!text) return;

    const success = await copyText(text);
    if (success) copiedState.trigger();
  }
</script>

{#if text}
  <div class="inline-flex items-center gap-2 {className}">
    <button
      onclick={handleCopy}
      class="underline underline-offset-2 decoration-dotted decoration-foreground-muted hover:decoration-solid transition-all duration-200 cursor-pointer"
      title={copiedState.active ? 'Copied!' : 'Click to copy'}
    >
      {text}
    </button>
    <button
      onclick={handleCopy}
      class="p-1 rounded-md transition-all duration-200 {copiedState.active
        ? 'text-success-text bg-success/15'
        : 'text-foreground-muted hover:text-foreground-soft hover:bg-muted'}"
      title={copiedState.active ? 'Copied!' : 'Copy to clipboard'}
    >
      <Icon
        icon={copiedState.active ? 'lucide:check' : 'lucide:copy'}
        class="h-4 w-4"
      />
    </button>
  </div>
{/if}
