<script lang="ts">
  import { ShareExpirationState } from '@slink/feature/Share';
  import { DatePickerField } from '@slink/ui/components/date-picker';
  import { Switch } from '@slink/ui/components/switch';

  import { plural } from '$lib/utils/i18n';
  import Icon from '@iconify/svelte';
  import { fly, slide } from 'svelte/transition';

  import type { ShareStatusKind } from '../share.theme';
  import { statusIconName, status as statusTheme } from '../share.theme';
  import { controls } from './Popover.theme';

  interface Props {
    expirationState: ShareExpirationState;
    onBack: () => void;
  }

  let { expirationState, onBack }: Props = $props();

  let customRevealed: boolean = $state(false);

  const isCustomActive = $derived.by<boolean>(() => {
    if (!expirationState.enabled) {
      return false;
    }

    if (customRevealed) {
      return true;
    }

    return (
      expirationState.date !== null && expirationState.activePresetDays === null
    );
  });

  const handlePreset = (days: number): void => {
    customRevealed = false;
    expirationState.setFromDays(days);
  };

  const handleCustomSelect = (): void => {
    customRevealed = true;
  };

  const handleToggle = (checked: boolean): void => {
    expirationState.toggle(checked);
  };

  const statusKind = $derived<ShareStatusKind | null>(
    expirationState.status?.kind ?? null,
  );

  const detail = $derived(controls.detail({ chipActive: isCustomActive }));
  const status = $derived(
    statusTheme({
      kind: statusKind ?? undefined,
      spinning: statusKind === 'saving',
    }),
  );
</script>

<div in:fly|local={{ x: 6, duration: 120 }} class={detail.root()}>
  <div class={detail.header()}>
    <button
      type="button"
      class={detail.back()}
      onclick={onBack}
      aria-label="Back to options"
    >
      <Icon icon="ph:caret-left" class={detail.backIcon()} />
    </button>

    <div class={detail.labels()}>
      <div class={detail.titleRow()}>
        <div class={detail.titleGroup()}>
          <span class={detail.title()}>Expiration</span>
          {#if expirationState.status !== null && statusKind !== null}
            <span
              class={status.line()}
              aria-live="polite"
              title={expirationState.status.message}
            >
              <Icon icon={statusIconName(statusKind)} class={status.icon()} />
            </span>
          {/if}
        </div>
        <Switch
          checked={expirationState.enabled}
          onCheckedChange={handleToggle}
        />
      </div>
      <span class={detail.description()}>
        Restrict link availability after the chosen date
      </span>
    </div>
  </div>

  {#if expirationState.enabled}
    <div transition:slide={{ duration: 180 }} class={detail.body()}>
      <div class={detail.presets()}>
        {#each ShareExpirationState.PRESET_DAYS as days (days)}
          <button
            type="button"
            class={controls
              .detail({
                chipActive: expirationState.activePresetDays === days,
              })
              .chip()}
            onclick={() => handlePreset(days)}
            disabled={expirationState.isSaving}
          >
            {plural(days, ['# day', '# days'])}
          </button>
        {/each}
        <button
          type="button"
          class={detail.chip()}
          onclick={handleCustomSelect}
          disabled={expirationState.isSaving}
        >
          Custom
        </button>
      </div>

      {#if isCustomActive}
        <div transition:slide={{ duration: 180 }}>
          <DatePickerField
            bind:value={expirationState.date}
            placeholder="Pick a date"
            disabled={expirationState.isSaving}
            class={detail.field()}
          />
        </div>
      {/if}
    </div>
  {/if}
</div>
