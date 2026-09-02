<script lang="ts">
  import type { Snippet } from 'svelte';

  import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
  import { copyText } from '$lib/utils/ui/clipboard';

  interface CopyState {
    copied: boolean;
    copy: () => Promise<void>;
  }

  interface Props {
    text: string;
    delay?: number;
    children: Snippet<[CopyState]>;
  }

  let { text, delay = 1500, children }: Props = $props();

  const copied = useAutoReset(delay);

  const copy = async (): Promise<void> => {
    const isCopied = await copyText(text);

    if (!isCopied) {
      return;
    }

    copied.trigger();
  };

  const state = $derived<CopyState>({ copied: copied.active, copy });
</script>

{@render children(state)}
