<script lang="ts">
  import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
  import Icon from '@iconify/svelte';

  interface Props {
    text: string;
    class?: string;
  }

  let { text, class: className = '' }: Props = $props();
  const copiedState = useAutoReset(2000);

  async function copyText() {
    try {
      await navigator.clipboard.writeText(text);
      copiedState.trigger();
    } catch (err) {
      console.error('Failed to copy text: ', err);
    }
  }
</script>

<div class="inline-flex items-center gap-2 {className}">
  <button
    onclick={copyText}
    class="underline underline-offset-2 decoration-dotted decoration-gray-400 dark:decoration-gray-500 hover:decoration-solid transition-all duration-200 cursor-pointer"
    title={copiedState.active ? 'Copied!' : 'Click to copy'}
  >
    {text}
  </button>
  <button
    onclick={copyText}
    class="p-1 rounded-md transition-all duration-200 {copiedState.active
      ? 'text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30'
      : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'}"
    title={copiedState.active ? 'Copied!' : 'Copy to clipboard'}
  >
    <Icon
      icon={copiedState.active ? 'lucide:check' : 'lucide:copy'}
      class="h-4 w-4"
    />
  </button>
</div>
