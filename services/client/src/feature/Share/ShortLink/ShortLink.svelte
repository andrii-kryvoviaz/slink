<script lang="ts">
  import { Copyable } from '@slink/ui/components/copyable';

  import Icon from '@iconify/svelte';

  import { routes } from '@slink/utils/url';

  import { shortLink } from './ShortLink.theme';

  interface Props {
    url: string;
  }

  let { url }: Props = $props();

  const theme = shortLink();

  const label = $derived(routes.share.shortLinkText(url));
</script>

<Copyable text={url}>
  {#snippet children({ copied, copy })}
    <button type="button" class={theme.copy()} onclick={copy}>
      <span class={theme.meta()}>{label}</span>
      <span class={theme.srOnly()}>Copy link</span>
      {#if copied}
        <Icon icon="ph:check-circle" class={theme.copyIcon({ copied: true })} />
      {:else}
        <Icon icon="ph:copy" class={theme.copyIcon()} />
      {/if}
    </button>
  {/snippet}
</Copyable>
