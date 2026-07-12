<script lang="ts">
  import { FormattedDate } from '@slink/feature/Text';
  import type { Snippet } from 'svelte';

  import { plural } from '$lib/utils/i18n';

  import ExpirationPicker from '../Controls/ExpirationPicker/ExpirationPicker.svelte';
  import PasswordPicker from '../Controls/PasswordPicker/PasswordPicker.svelte';
  import { controls } from '../Controls/Popover.theme';
  import { getShareControls } from '../State/Context';
  import ChipPopover from './ChipPopover.svelte';

  interface Props {
    prepend?: Snippet;
  }

  let { prepend }: Props = $props();

  const share = getShareControls();

  let expirationOpen = $state(false);
  let passwordOpen = $state(false);

  const expiration = $derived.by<{ set: boolean; label: string }>(() => {
    const short = share.expiration.descriptionShort;

    if (short === null) {
      return { set: false, label: 'Expiration' };
    }

    if (short.kind === 'expired') {
      return { set: true, label: 'Expired' };
    }

    if (short.kind === 'today') {
      return { set: true, label: 'Today' };
    }

    return { set: true, label: short.label };
  });

  const passwordSet = $derived(share.password.isProtected);

  const expiresTimestamp = $derived.by<number | null>(() => {
    const date = share.expiration.date;

    if (!share.expiration.enabled || date === null) {
      return null;
    }

    return Math.floor(date.getTime() / 1000);
  });

  const detail = controls.detail();
</script>

<div class="flex flex-wrap items-center gap-2">
  {@render prepend?.()}

  <ChipPopover
    bind:open={expirationOpen}
    state={expiration.set ? 'set' : 'ghost'}
    label={expiration.label}
    removeLabel="Remove expiration"
    onRemove={() => share.expiration.toggle(false)}
  >
    <div class={detail.root()}>
      <ExpirationPicker onApply={() => (expirationOpen = false)} />
      {#if expiresTimestamp !== null}
        <p class={detail.footerHint()}>
          Link expires <FormattedDate
            date={expiresTimestamp}
            showTime={false}
          />
        </p>
      {/if}
    </div>
  </ChipPopover>

  <ChipPopover
    bind:open={passwordOpen}
    state={passwordSet ? 'set' : 'ghost'}
    label={passwordSet ? 'Protected' : 'Password'}
    removeLabel="Remove password"
    onRemove={() => share.password.toggle(false)}
  >
    <div class={detail.root()}>
      <PasswordPicker autofocus onApply={() => (passwordOpen = false)}>
        {#snippet hint()}
          <p class={detail.footerHint()}>
            At least {plural(share.password.minLength, [
              '# character',
              '# characters',
            ])}
          </p>
        {/snippet}
      </PasswordPicker>
    </div>
  </ChipPopover>
</div>
