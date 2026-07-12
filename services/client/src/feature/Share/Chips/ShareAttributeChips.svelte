<script lang="ts">
  import type { Snippet } from 'svelte';

  import ExpirationDetail from '../Controls/ExpirationDetail.svelte';
  import PasswordDetail from '../Controls/PasswordDetail.svelte';
  import { getShareControls } from '../State/Context';
  import ChipPopover from './ChipPopover.svelte';

  interface Props {
    prepend?: Snippet;
  }

  let { prepend }: Props = $props();

  const share = getShareControls();

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
</script>

<div class="flex flex-wrap items-center gap-2">
  {@render prepend?.()}

  <ChipPopover
    state={expiration.set ? 'set' : 'ghost'}
    label={expiration.label}
    removeLabel="Remove expiration"
    onRemove={() => share.expiration.toggle(false)}
  >
    <ExpirationDetail />
  </ChipPopover>

  <ChipPopover
    state={passwordSet ? 'set' : 'ghost'}
    label={passwordSet ? 'Protected' : 'Password'}
    removeLabel="Remove password"
    onRemove={() => share.password.toggle(false)}
  >
    <PasswordDetail />
  </ChipPopover>
</div>
