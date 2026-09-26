<script lang="ts">
  import { CopyableText } from '@slink/feature/Text';

  import { page } from '$app/state';

  import { cn } from '@slink/utils/ui';
  import { routes } from '@slink/utils/url';

  interface Props {
    class?: string;
  }

  let { class: className }: Props = $props();
</script>

<div
  class={cn(
    'inline-flex max-w-full flex-wrap items-center gap-x-2 gap-y-1 rounded-lg border-[0.5px] border-muted bg-muted-soft px-2.5 py-1',
    className,
  )}
>
  <span class="text-xs text-foreground-muted">Callback URL</span>
  <CopyableText
    text={routes.sso.callbackUrl(page.url.origin)}
    class="min-w-0 break-all font-mono text-xs text-foreground"
  />
</div>
