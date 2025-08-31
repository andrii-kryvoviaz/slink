<script lang="ts">
  import { KeyboardKey, type KeyboardKeyProps } from '@slink/feature/Text';

  import { browser } from '$app/environment';
  import { page } from '$app/state';

  import { parseUserAgent } from '@slink/utils/http/userAgentParser';
  import type { RequireAtLeastOne } from '@slink/utils/object/requiredAtLeastOne.type';

  interface BaseProps extends KeyboardKeyProps {
    class?: string;
    control?: boolean;
    alt?: boolean;
    shift?: boolean;
    key: string;
    onHit?: () => void;
  }

  type Props = RequireAtLeastOne<BaseProps, 'control' | 'alt' | 'shift'>;

  let {
    control = false,
    alt = false,
    shift = false,
    key = '',
    onHit = undefined,
    ...props
  }: Props = $props();

  let userAgentInfo = parseUserAgent(page.data.userAgent);

  if (onHit && typeof onHit === 'function' && browser) {
    window.addEventListener('keydown', (event: KeyboardEvent) => {
      if (
        event.key === key.toLocaleLowerCase() &&
        ((userAgentInfo.isMacOS || userAgentInfo.isIOS) === control ||
          (!userAgentInfo.isMacOS && !userAgentInfo.isIOS && control)) &&
        ((event.altKey && alt) || (!alt && !event.altKey)) &&
        ((event.shiftKey && shift) || (!shift && !event.shiftKey))
      ) {
        onHit();
      }
    });
  }
</script>

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
