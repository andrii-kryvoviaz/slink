<script lang="ts">
  import { ShareExpirationState } from '@slink/feature/Share';
  import { DatePickerField } from '@slink/ui/components/date-picker';
  import { Switch } from '@slink/ui/components/switch';

  import { plural } from '$lib/utils/i18n';
  import Icon from '@iconify/svelte';
  import { fly, slide } from 'svelte/transition';

  import { getShareControls } from '../State/Context';
  import type { ShareStatusKind } from '../share.theme';
  import { statusIconName, status as statusTheme } from '../share.theme';
  import { controls } from './Popover.theme';

  interface Props {
    onBack: () => void;
  }

  let { onBack }: Props = $props();

  const expiration = getShareControls().expiration;

  let customRevealed: boolean = $state(false);

  const isCustomActive = $derived.by<boolean>(() => {
    if (!expiration.enabled) {
      return false;
    }

    if (customRevealed) {
      return true;
    }

    return expiration.date !== null && expiration.activePresetDays === null;
  });

  const handlePreset = (days: number): void => {
    customRevealed = false;
    expiration.setFromDays(days);
  };

  const handleCustomSelect = (): void => {
    customRevealed = true;
  };

  const handleToggle = (checked: boolean): void => {
    expiration.toggle(checked);
  };

  const statusKind = $derived<ShareStatusKind | null>(
    expiration.status?.kind ?? null,
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
          {#if expiration.status !== null && statusKind !== null}
            <span
              class={status.line()}
              aria-live="polite"
              title={expiration.status.message}
            >
              <Icon icon={statusIconName(statusKind)} class={status.icon()} />
            </span>
          {/if}
        </div>
        <Switch checked={expiration.enabled} onCheckedChange={handleToggle} />
      </div>
      <span class={detail.description()}>
        Restrict link availability after the chosen date
      </span>
    </div>
  </div>

  {#if expiration.enabled}
    <div transition:slide={{ duration: 180 }} class={detail.body()}>
      <div class={detail.presets()}>
        {#each ShareExpirationState.PRESET_DAYS as days (days)}
          <button
            type="button"
            class={controls
              .detail({
                chipActive: expiration.activePresetDays === days,
              })
              .chip()}
            onclick={() => handlePreset(days)}
            disabled={expiration.isSaving}
          >
            {plural(days, ['# day', '# days'])}
          </button>
        {/each}
        <button
          type="button"
          class={detail.chip()}
          onclick={handleCustomSelect}
          disabled={expiration.isSaving}
        >
          Custom
        </button>
      </div>

      {#if isCustomActive}
        <div transition:slide={{ duration: 180 }}>
          <DatePickerField
            bind:value={expiration.date}
            placeholder="Pick a date"
            disabled={expiration.isSaving}
            class={detail.field()}
          />
        </div>
      {/if}
    </div>
  {/if}
</div>
