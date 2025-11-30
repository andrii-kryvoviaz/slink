<script lang="ts">
  import { KeyboardKey } from '@slink/feature/Text';
  import type { KeyboardKeyProps } from '@slink/feature/Text';

  import { browser } from '$app/environment';
  import { page } from '$app/state';

  import { parseUserAgent } from '@slink/utils/http/userAgentParser';

  interface Props extends KeyboardKeyProps {
    class?: string;
    control?: boolean;
    alt?: boolean;
    shift?: boolean;
    key: string;
    onHit?: () => void;
    enabled?: boolean;
    hidden?: boolean;
  }

  let {
    control = false,
    alt = false,
    shift = false,
    key = '',
    onHit = undefined,
    enabled = true,
    hidden = false,
    ...props
  }: Props = $props();

  let userAgentInfo = parseUserAgent(page.data.userAgent);

  $effect(() => {
    if (!onHit || typeof onHit !== 'function' || !browser) return;

    const handleKeydown = (event: KeyboardEvent) => {
      if (!enabled) return;

      const keyMatches =
        event.key.toLowerCase() === key.toLowerCase() ||
        event.code.toLowerCase() === key.toLowerCase();

      const controlMatches = control
        ? userAgentInfo.isMacOS || userAgentInfo.isIOS
          ? event.metaKey
          : event.ctrlKey
        : !(event.metaKey || event.ctrlKey);
      const altMatches = alt ? event.altKey : !event.altKey;
      const shiftMatches = shift ? event.shiftKey : !event.shiftKey;

      if (keyMatches && controlMatches && altMatches && shiftMatches) {
        event.preventDefault();
        onHit();
      }
    };

    window.addEventListener('keydown', handleKeydown);
    return () => window.removeEventListener('keydown', handleKeydown);
  });
</script>

{#if !hidden}
  <div class="flex items-center gap-2 justify-center">
    {#if control}
      <KeyboardKey {...props}>
        {#if userAgentInfo.isMacOS || userAgentInfo.isIOS}
          ⌘ cmd
        {:else}
          Ctrl
        {/if}
      </KeyboardKey>
      <span>+</span>
    {/if}
    {#if alt}
      <KeyboardKey {...props}>Alt</KeyboardKey>
      <span>+</span>
    {/if}
    {#if shift}
      <KeyboardKey {...props}>Shift</KeyboardKey>
      <span>+</span>
    {/if}
    <KeyboardKey {...props}>
      {key}
    </KeyboardKey>
  </div>
{/if}
