<script lang="ts">
  import { ShareExpirationState } from '@slink/feature/Share';
  import { DatePickerField } from '@slink/ui/components/date-picker';

  import { plural } from '$lib/utils/i18n';
  import { slide } from 'svelte/transition';

  import { getShareControls } from '../../State/Context';
  import { controls } from '../Popover.theme';

  interface Props {
    onApply?: () => void;
  }

  let { onApply }: Props = $props();

  const expiration = getShareControls().expiration;

  let customRevealed: boolean = $state(false);

  const isCustomActive = $derived(
    customRevealed ||
      (expiration.enabled &&
        expiration.date !== null &&
        expiration.activePresetDays === null),
  );

  const handlePreset = (days: number): void => {
    customRevealed = false;
    expiration.toggle(true);
    expiration.setFromDays(days);
    onApply?.();
  };

  const handleCustomSelect = (): void => {
    customRevealed = true;
  };

  const applyDate = (value: Date | null): void => {
    if (value === null) {
      expiration.date = null;
      return;
    }

    expiration.toggle(true);
    expiration.date = value;
    onApply?.();
  };

  const detail = $derived(controls.detail({ chipActive: isCustomActive }));
</script>

<div class={detail.body()}>
  <div class={detail.presets()}>
    {#each ShareExpirationState.PRESET_DAYS as days (days)}
      <button
        type="button"
        class={controls
          .detail({
            chipActive:
              expiration.enabled && expiration.activePresetDays === days,
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
        bind:value={() => expiration.date, applyDate}
        placeholder="Pick a date"
        disabled={expiration.isSaving}
        class={detail.field()}
      />
    </div>
  {/if}
</div>
