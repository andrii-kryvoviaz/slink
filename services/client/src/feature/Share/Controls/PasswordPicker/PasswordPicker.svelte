<script lang="ts">
  import { PasswordToggle } from '@slink/feature/Auth';
  import type { Snippet } from 'svelte';

  import { getShareControls } from '../../State/Context';
  import { controls } from '../Popover.theme';

  interface Props {
    autofocus?: boolean;
    onApply?: () => void;
    hint?: Snippet;
  }

  let { autofocus = false, onApply, hint }: Props = $props();

  const passwordState = getShareControls().password;

  let revealed: boolean = $state(false);
  let inputRef: HTMLInputElement | null = $state(null);

  const placeholder = $derived(
    passwordState.hasExistingPassword ? 'Replace password' : 'Enter a password',
  );

  $effect(() => {
    if (!autofocus) {
      return;
    }

    const frame = requestAnimationFrame(() => inputRef?.focus());

    return () => cancelAnimationFrame(frame);
  });

  const handleInput = (event: Event): void => {
    const target = event.currentTarget as HTMLInputElement;
    passwordState.setPassword(target.value);
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

    passwordState.toggle(true);
    await passwordState.commit();

    if (passwordState.status === 'error') {
      return;
    }

    revealed = false;
    onApply?.();
  };

  const detail = controls.detail();
</script>

<div class={detail.body()}>
  <form onsubmit={handleSubmit} class="flex flex-col gap-2">
    <div class={detail.fieldRow()}>
      <input
        bind:this={inputRef}
        type={revealed ? 'text' : 'password'}
        value={passwordState.password}
        {placeholder}
        autocomplete="new-password"
        spellcheck="false"
        disabled={passwordState.isSaving}
        oninput={handleInput}
        class={detail.fieldInput()}
      />
      <PasswordToggle
        inline
        visible={revealed}
        onclick={toggleReveal}
        size="sm"
      />
      <button
        type="submit"
        class={controls
          .detail({
            setEnabled:
              passwordState.isPasswordValid && !passwordState.isSaving,
          })
          .setAction()}
        aria-disabled={!passwordState.isPasswordValid}
      >
        Set
      </button>
    </div>
  </form>

  {@render hint?.()}
</div>
