<script lang="ts">
  import { PasswordToggle } from '@slink/feature/Auth';
  import { Button } from '@slink/ui/components/button';
  import { Input } from '@slink/ui/components/input';
  import { Switch } from '@slink/ui/components/switch';

  import { plural } from '$lib/utils/i18n';
  import Icon from '@iconify/svelte';
  import { fly, slide } from 'svelte/transition';

  import { getShareControls } from '../State/Context';
  import StatusIndicator from '../StatusIndicator/StatusIndicator.svelte';
  import type { ShareStatusKind } from '../share.theme';
  import { controls } from './Popover.theme';

  interface Props {
    onBack: () => void;
  }

  let { onBack }: Props = $props();

  const passwordState = getShareControls().password;

  let revealed: boolean = $state(false);
  let editing: boolean = $state(false);
  let inputRef: HTMLInputElement | null = $state(null);

  const showReplacePlaceholder = $derived(
    passwordState.enabled && passwordState.hasExistingPassword && !editing,
  );

  const showInput = $derived(passwordState.enabled && !showReplacePlaceholder);

  const showMinLengthHint = $derived(
    passwordState.enabled &&
      !showReplacePlaceholder &&
      passwordState.password.length > 0 &&
      !passwordState.isPasswordValid,
  );

  const handleToggle = (checked: boolean): void => {
    if (!checked) {
      editing = false;
      revealed = false;
    }

    passwordState.toggle(checked);
  };

  const handleInput = (event: Event): void => {
    const target = event.currentTarget as HTMLInputElement;
    passwordState.setPassword(target.value);
  };

  const handleReplace = async (): Promise<void> => {
    editing = true;
    await Promise.resolve();
    inputRef?.focus();
  };

  const toggleReveal = (): void => {
    revealed = !revealed;
  };

  const handleSubmit = async (event: Event): Promise<void> => {
    event.preventDefault();

    if (!passwordState.isPasswordValid) {
      return;
    }

    if (passwordState.isSaving) {
      return;
    }

    await passwordState.commit();

    if (passwordState.status === 'error') {
      return;
    }

    editing = false;
    revealed = false;
  };

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

  const detail = $derived(controls.detail());
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
          <span class={detail.title()}>Password</span>
          <StatusIndicator kind={statusKind} title={statusTitle} />
        </div>
        <Switch
          checked={passwordState.enabled}
          onCheckedChange={handleToggle}
        />
      </div>
      <span class={detail.description()}> Require a password to view </span>
    </div>
  </div>

  {#if passwordState.enabled}
    <div transition:slide={{ duration: 180 }} class={detail.body()}>
      {#if showReplacePlaceholder}
        <div class={detail.placeholderRow()}>
          <span class={detail.placeholderDots()} aria-hidden="true">
            •••••••
          </span>
          <button
            type="button"
            class={detail.replaceButton()}
            onclick={handleReplace}
            disabled={passwordState.isSaving}
          >
            Replace password
          </button>
        </div>
      {/if}

      {#if showInput}
        <form onsubmit={handleSubmit} class="flex flex-col gap-2">
          <Input
            bind:ref={inputRef}
            type={revealed ? 'text' : 'password'}
            value={passwordState.password}
            placeholder="Enter a password"
            autocomplete="new-password"
            spellcheck="false"
            disabled={passwordState.isSaving}
            oninput={handleInput}
            variant="modern"
            size="sm"
            rounded="md"
          >
            <PasswordToggle
              visible={revealed}
              onclick={toggleReveal}
              size="sm"
            />
          </Input>

          <Button
            type="submit"
            variant="primary-dark"
            size="sm"
            rounded="md"
            disabled={!passwordState.isPasswordValid}
            loading={passwordState.isSaving}
          >
            Set password
          </Button>
        </form>
      {/if}

      {#if showMinLengthHint}
        <p class={detail.passwordHint()}>
          Use at least {plural(passwordState.minLength, [
            '# character',
            '# characters',
          ])}
        </p>
      {:else}
        <p class={detail.passwordHelper()}>
          Anyone with the link will need this password to view
        </p>
      {/if}
    </div>
  {/if}
</div>
