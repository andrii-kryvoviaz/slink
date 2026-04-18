<script lang="ts">
  import { ApiClient } from '@slink/api';
  import * as Share from '@slink/feature/Share';
  import { CopyContainer } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import type { ShareResponse } from '@slink/api/Response/Share/ShareResponse';

  interface TriggerState {
    isShared: boolean;
    isLoading: boolean;
  }

  interface Props {
    collectionId: string;
    open?: boolean;
    initialShare?: ShareResponse | null;
    trigger: Snippet<[TriggerState]>;
    triggerClass?: string;
  }

  let {
    collectionId,
    open = $bindable(false),
    initialShare = null,
    trigger: triggerSnippet,
    triggerClass,
  }: Props = $props();

  const share = Share.createShare({
    fetchShare: () => ApiClient.collection.share(collectionId),
    initial: initialShare,
  });

  const theme = Share.controls.intro();
</script>

<Share.Popover
  expirationState={share.expiration}
  bind:open
  width="w-80 p-3"
  triggerLabel="Share collection"
  {triggerClass}
  introActive={!share.isInitialized}
>
  {#snippet trigger()}
    {@render triggerSnippet({
      isShared: share.isInitialized,
      isLoading: share.isLoading,
    })}
  {/snippet}

  {#snippet intro()}
    <div class={theme.wrap()}>
      <Share.AccentIcon size="lg">
        <Icon icon="ph:link-simple-fill" class="h-5 w-5" />
      </Share.AccentIcon>
      <h3 class={theme.title()}>Share this collection</h3>
      <p class={theme.description()}>
        Create a public link. Anyone with the link will be able to view this
        collection.
      </p>
      <div class={theme.actions()}>
        <Button
          variant="outline-blue"
          rounded="full"
          size="sm"
          class="w-full font-medium"
          loading={share.isLoading}
          onclick={share.load}
        >
          Create share link
        </Button>
      </div>
    </div>
  {/snippet}

  {#snippet header()}
    {#if share.shareUrl !== null}
      <CopyContainer
        value={share.shareUrl}
        size="sm"
        variant="default"
        onBeforeCopy={share.ensurePublished}
      />
    {/if}
  {/snippet}
</Share.Popover>
