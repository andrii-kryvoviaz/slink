<script lang="ts">
  import { plural } from '$lib/utils/i18n';
  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { getShareControls } from '../State/Context';
  import StatusIndicator from '../StatusIndicator/StatusIndicator.svelte';
  import type { ShareStatusKind } from '../share.theme';
  import PasswordPicker from './PasswordPicker/PasswordPicker.svelte';
  import { controls } from './Popover.theme';

  interface Props {
    onBack?: () => void;
    onApply?: () => void;
  }

  let { onBack, onApply }: Props = $props();

  const passwordState = getShareControls().password;

  const statusKind = $derived<ShareStatusKind | null>(passwordState.status);

  const statusTitle = $derived.by<string>(() => {
    if (statusKind === 'saving') {
      return 'Saving password';
    }

    if (statusKind === 'error') {
      return 'Failed to save password';
    }

    return 'Saved';
  });

  const handleRemove = (): void => {
    passwordState.toggle(false);
    onApply?.();
  };

  const detail = controls.detail();
</script>

<div in:fly|local={{ x: 6, duration: 120 }} class={detail.root()}>
  <div class={detail.header()}>
    {#if onBack}
      <button
        type="button"
        class={detail.back()}
        onclick={onBack}
        aria-label="Back to options"
      >
        <Icon icon="ph:caret-left" class={detail.backIcon()} />
      </button>
    {/if}

    <div class={detail.labels()}>
      <div class={detail.titleRow()}>
        <div class={detail.titleGroup()}>
          <span class={detail.title()}>Password</span>
          <StatusIndicator kind={statusKind} title={statusTitle} />
        </div>
        {#if passwordState.isProtected}
          <button
            type="button"
            class={detail.removeAction()}
            onclick={handleRemove}
          >
            Remove
          </button>
        {/if}
      </div>
    </div>
  </div>

  <PasswordPicker autofocus {onApply}>
    {#snippet hint()}
      <p class={detail.footerHint()}>
        At least {plural(passwordState.minLength, [
          '# character',
          '# characters',
        ])}
      </p>
    {/snippet}
  </PasswordPicker>
</div>
