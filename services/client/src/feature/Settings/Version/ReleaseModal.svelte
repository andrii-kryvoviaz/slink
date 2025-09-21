<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import * as Dialog from '@slink/ui/components/dialog';
  import { ScrollArea } from '@slink/ui/components/scroll-area';

  import type { GitHubRelease } from '$lib/utils/version';
  import Icon from '@iconify/svelte';

  interface Props {
    open: boolean;
    release: GitHubRelease;
    currentVersion: string;
    hasUpdate: boolean;
    onClose: () => void;
  }

  let {
    open = $bindable(),
    release,
    currentVersion,
    hasUpdate,
    onClose,
  }: Props = $props();

  function handleOpenChange(newOpen: boolean) {
    if (!newOpen) {
      onClose();
    }
  }

  function formatReleaseDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  }

  function formatChangelog(body: string): string {
    return body
      .replace(/\*\*Full Changelog\*\*.*$/s, '')
      .replace(
        /### (.*)/g,
        '<h3 class="text-lg font-semibold first:mt-0 mt-4 mb-2">$1</h3>',
      )
      .replace(
        /## (.*)/g,
        '<h2 class="text-xl font-bold first:mt-0 mt-6 mb-3">$1</h2>',
      )
      .replace(
        /# (.*)/g,
        '<h1 class="text-2xl font-bold first:mt-0 mt-8 mb-4">$1</h1>',
      )
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/\*(.*?)\*/g, '<em>$1</em>')
      .replace(
        /`(.*?)`/g,
        '<code class="bg-muted px-1 py-0.5 rounded text-sm">$1</code>',
      )
      .replace(/- (.*)/g, '<li class="ml-4 my-2">$1</li>');
  }
</script>

<Dialog.Root {open} onOpenChange={handleOpenChange}>
  <Dialog.Content class="max-w-2xl max-h-[80vh]">
    <Dialog.Header>
      <Dialog.Title class="flex items-center gap-3">
        <div class="p-2 bg-primary/10 rounded-lg">
          <Icon
            icon={hasUpdate ? 'ph:download' : 'ph:check-circle'}
            class="h-5 w-5 text-primary"
          />
        </div>
        {hasUpdate ? 'Update Available' : 'Latest Version'}
      </Dialog.Title>
      <Dialog.Description>
        {#if hasUpdate}
          A new version of Slink is available. Here's what's new:
        {:else}
          You're running the latest version of Slink. Here are the release
          notes:
        {/if}
      </Dialog.Description>
    </Dialog.Header>

    <div class="space-y-4">
      <div
        class="flex items-center justify-between p-4 bg-primary/5 border border-primary/20 rounded-lg"
      >
        <div>
          <div class="text-sm text-muted-foreground">Current Version</div>
          <div class="font-mono font-medium">v{currentVersion}</div>
        </div>
        {#if hasUpdate}
          <Icon icon="ph:arrow-right" class="h-4 w-4 text-primary/60" />
          <div>
            <div class="text-sm text-muted-foreground">Latest Version</div>
            <div class="font-mono font-medium text-primary">
              {release.tag_name}
            </div>
          </div>
        {:else}
          <div
            class="flex items-center gap-2 text-green-600 dark:text-green-400"
          >
            <Icon icon="ph:check-circle" class="h-4 w-4" />
            <span class="text-sm font-medium">Up to date</span>
          </div>
        {/if}
      </div>

      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">{release.name}</h3>
          <div class="text-sm text-muted-foreground">
            Released {formatReleaseDate(release.published_at)}
          </div>
        </div>

        <ScrollArea class="h-64 w-full border rounded-lg p-4">
          <div class="prose prose-sm max-w-none dark:prose-invert">
            {@html formatChangelog(release.body)}
          </div>
        </ScrollArea>
      </div>
    </div>

    <Dialog.Footer class="flex gap-3">
      <Button
        variant="glass"
        size="md"
        onclick={onClose}
        class="flex-1 h-11 rounded-xl shadow-sm hover:shadow-md transition-all duration-200"
      >
        <Icon icon="ph:x" class="h-4 w-4 mr-2" />
        {hasUpdate ? 'Maybe Later' : 'Close'}
      </Button>
      <Button
        variant="gradient-blue"
        size="md"
        onclick={() => window.open(release.html_url, '_blank')}
        class="flex-1 h-11 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200"
      >
        <Icon icon="ph:github-logo" class="h-4 w-4 mr-2" />
        {hasUpdate ? 'View Release' : 'View on GitHub'}
      </Button>
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
