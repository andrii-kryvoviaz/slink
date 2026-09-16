<script lang="ts">
  import { Copyable } from '@slink/ui/components/copyable';

  import Icon from '@iconify/svelte';

  interface Props {
    text: string | null;
    class?: string;
  }

  let { text, class: className = '' }: Props = $props();
</script>

{#if text}
  <Copyable {text} delay={2000}>
    {#snippet children({ copied, copy })}
      <div class="inline-flex items-center gap-2 {className}">
        <button
          onclick={copy}
          class="underline underline-offset-2 decoration-dotted decoration-foreground-muted hover:decoration-solid transition-all duration-200 cursor-pointer"
          title={copied ? 'Copied!' : 'Click to copy'}
        >
          {text}
        </button>
        <button
          onclick={copy}
          class="p-1 rounded-md transition-all duration-200 {copied
            ? 'text-success-text bg-success/15'
            : 'text-foreground-muted hover:text-foreground-soft hover:bg-muted'}"
          title={copied ? 'Copied!' : 'Copy to clipboard'}
        >
          <Icon
            icon={copied ? 'lucide:check' : 'lucide:copy'}
            class="h-4 w-4"
          />
        </button>
      </div>
    {/snippet}
  </Copyable>
{/if}
