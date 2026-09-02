<script lang="ts">
  import { Badge } from '@slink/feature/Text';
  import { Tooltip, TooltipProvider } from '@slink/ui/components/tooltip';
  import { onMount } from 'svelte';

  import { UpdateCheckState } from '$lib/state';
  import { versionStore } from '$lib/stores/version.svelte';
  import { formatVersion, getVersionInfo } from '$lib/utils/version';

  import ReleaseModal from './ReleaseModal.svelte';

  interface Props {
    showUpdateIndicator?: boolean;
    className?: string;
  }

  let { showUpdateIndicator = false, className = '' }: Props = $props();

  let showReleaseModal = $state(false);

  const versionInfo = $derived($versionStore);

  const release = $derived.by(() => {
    if (UpdateCheckState.hasUpdate && UpdateCheckState.latestRelease) {
      return UpdateCheckState.latestRelease;
    }

    if (
      !UpdateCheckState.hasUpdate &&
      UpdateCheckState.latestRelease &&
      versionInfo
    ) {
      const currentVersion = versionInfo.version.replace(/^v/, '');
      const latestVersion = UpdateCheckState.latestRelease.tag_name.replace(
        /^v/,
        '',
      );

      if (currentVersion === latestVersion) {
        return UpdateCheckState.latestRelease;
      }
    }

    return null;
  });

  const isClickable = $derived(showUpdateIndicator && release !== null);

  if (!$versionStore) {
    versionStore.set(getVersionInfo());
  }

  onMount(async () => {
    if (showUpdateIndicator && $versionStore) {
      await UpdateCheckState.checkForUpdates($versionStore);
    }
  });

  function handleVersionClick() {
    if (isClickable) {
      showReleaseModal = true;
    }
  }
</script>

<TooltipProvider delayDuration={500}>
  <Tooltip variant="default" size="sm" side="top" sideOffset={4}>
    {#snippet trigger()}
      <button
        class={`flex items-center gap-3 ${className} text-left p-1 -m-1 rounded-md hover:bg-muted/30 transition-colors cursor-pointer disabled:cursor-default disabled:hover:bg-transparent`}
        onclick={handleVersionClick}
        disabled={!isClickable}
      >
        <div class="flex items-center gap-2 min-w-0 flex-1">
          {#if versionInfo}
            <span
              class="text-xs font-mono text-foreground/80 tracking-wide truncate hover:text-foreground/90 transition-colors"
            >
              {formatVersion(versionInfo)}
            </span>
          {/if}
          {#if UpdateCheckState.hasUpdate}
            <Badge
              variant="default"
              size="xs"
              class="bg-accent-wash dark:bg-accent-wash/20 text-accent-text border-accent-border dark:border-accent-border/30 hover:bg-accent/25 transition-colors"
            >
              Update Available
            </Badge>
          {:else if isClickable}
            <Badge
              variant="default"
              size="xs"
              class="bg-success-wash dark:bg-success-wash/20 text-success-text border-success/30"
            >
              Latest
            </Badge>
          {/if}
        </div>
      </button>
    {/snippet}
    {#if isClickable}
      <span
        >Click to view {UpdateCheckState.hasUpdate
          ? 'update details'
          : 'release notes'}</span
      >
    {:else}
      <span>Version {versionInfo ? formatVersion(versionInfo) : ''}</span>
    {/if}
  </Tooltip>
</TooltipProvider>

{#if release && versionInfo}
  <ReleaseModal
    bind:open={showReleaseModal}
    {release}
    currentVersion={versionInfo.version}
    hasUpdate={UpdateCheckState.hasUpdate}
    onClose={() => (showReleaseModal = false)}
  />
{/if}
