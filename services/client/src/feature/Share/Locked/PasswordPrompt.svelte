<script lang="ts">
  import { PasswordToggle } from '@slink/feature/Auth';
  import { Button } from '@slink/ui/components/button';
  import { Input } from '@slink/ui/components/input';
  import { tick } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { createShareUnlockState } from './State.svelte';

  interface Props {
    shareId: string;
  }

  let { shareId }: Props = $props();

  let inputRef: HTMLInputElement | null = $state(null);

  const focusInput = async (): Promise<void> => {
    await tick();
    inputRef?.focus();
  };

  const unlock = createShareUnlockState({
    shareId,
    onFailure: focusInput,
  });

  $effect(() => {
    void focusInput();
  });

  const handleFormSubmit = (event: Event): void => {
    event.preventDefault();
    void unlock.submit();
  };
</script>

<div class="flex items-center justify-center min-h-[60vh] px-4">
  <div
    class="flex flex-col items-center text-center w-full max-w-sm"
    in:fade={{ duration: 600, delay: 100 }}
  >
    <div
      class="relative w-20 h-20 mb-8 rounded-3xl flex items-center justify-center border backdrop-blur-sm shadow-lg bg-gradient-to-br from-slate-100/80 to-slate-200/60 dark:from-slate-800/60 dark:to-slate-700/40 border-slate-200/50 dark:border-slate-700/30 shadow-slate-200/20 dark:shadow-slate-900/40"
      in:fly={{ y: -20, duration: 500, delay: 200 }}
    >
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.1)_1px,transparent_1px)] bg-[length:12px_12px] rounded-3xl"
      ></div>

      <Icon
        icon="ph:lock-key-duotone"
        class="relative z-10 w-10 h-10 text-slate-600 dark:text-slate-400"
      />

      <div
        class="absolute inset-0 rounded-3xl bg-gradient-to-br from-white/5 to-transparent dark:from-white/10"
      ></div>
    </div>

    <div class="space-y-3 mb-8" in:fly={{ y: 20, duration: 500, delay: 300 }}>
      <h2
        class="text-xl font-semibold bg-gradient-to-r bg-clip-text text-transparent from-slate-800 to-slate-600 dark:from-slate-200 dark:to-slate-400"
      >
        This share is protected
      </h2>
      <p
        class="text-base text-slate-600 dark:text-slate-400 max-w-md mx-auto leading-relaxed"
      >
        Enter the password below to unlock the shared content.
      </p>
    </div>

    <form
      onsubmit={handleFormSubmit}
      class="w-full flex flex-col gap-4"
      in:fly={{ y: 20, duration: 500, delay: 400 }}
    >
      <Input
        bind:ref={inputRef}
        bind:value={unlock.password}
        type={unlock.revealed ? 'text' : 'password'}
        placeholder="Password"
        autocomplete="current-password"
        aria-label="Password"
        variant="modern"
        size="lg"
        rounded="lg"
      >
        {#snippet leftIcon()}
          <Icon
            icon="ph:lock-simple"
            class="text-gray-400 dark:text-gray-500"
          />
        {/snippet}
        <PasswordToggle
          visible={unlock.revealed}
          onclick={unlock.toggleReveal}
        />
      </Input>

      <Button
        variant="accent"
        size="lg"
        class="w-full mt-2 group"
        type="submit"
        disabled={unlock.isDisabled}
        loading={unlock.isSubmitting}
      >
        Continue
        {#snippet rightIcon()}
          <Icon
            icon="ph:arrow-right"
            class="w-5 h-5 ml-1 transition-transform duration-200 group-hover:translate-x-1"
          />
        {/snippet}
      </Button>
    </form>
  </div>
</div>
