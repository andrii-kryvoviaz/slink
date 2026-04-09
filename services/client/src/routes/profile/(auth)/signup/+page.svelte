<script lang="ts">
  import { PasswordStrength, PasswordToggle } from '@slink/feature/Auth';
  import {
    Banner,
    BannerAction,
    BannerContent,
    BannerIcon,
  } from '@slink/feature/Layout';
  import { Button } from '@slink/ui/components/button';
  import { Input } from '@slink/ui/components/input';

  import { enhance } from '$app/forms';
  import { t } from '$lib/i18n';
  import { translateApiErrorMessage } from '$lib/utils/error/translateApiErrorMessage';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { withLoadingState } from '@slink/utils/form/withLoadingState';
  import { useWritable } from '@slink/utils/store/contextAwareStore';
  import { toast } from '@slink/utils/ui/toast-sonner.svelte';

  interface Props {
    form: any;
  }

  let { form }: Props = $props();

  let isLoading = useWritable('signUpFormLoadingState', false);
  let passwordValue = $state('');
  let showPassword = $state(false);
  let showConfirmPassword = $state(false);

  $effect(() => {
    if (form?.errors?.message) {
      toast.error(translateApiErrorMessage(form.errors.message));
    }
  });
</script>

<svelte:head>
  <title>{$t('auth.signup.page_title')}</title>
</svelte:head>

<div
  class="w-full max-w-lg mx-auto px-6 py-8"
  in:fly={{ y: 20, duration: 500, delay: 100 }}
>
  <div class="flex items-center justify-start gap-4 mb-8">
    <div
      class="w-12 h-12 rounded-xl bg-linear-to-br from-primary/10 to-primary/5 border border-primary/10 flex items-center justify-center shadow-sm"
    >
      <img class="h-6 w-6" src="/favicon.png" alt="Slink" />
    </div>
    <div class="text-left">
      <h1
        class="text-2xl font-semibold text-gray-900 dark:text-white tracking-tight"
      >
        {$t('auth.signup.title')}
      </h1>
      <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">
        {$t('auth.signup.subtitle')}
      </p>
    </div>
  </div>

  <div
    class="bg-white/60 dark:bg-gray-900/40 backdrop-blur-sm rounded-2xl border border-gray-200/60 dark:border-gray-700/40 p-6 shadow-sm"
  >
    <form
      class="space-y-5"
      method="POST"
      use:enhance={withLoadingState(isLoading)}
      in:fade={{ duration: 400, delay: 200 }}
    >
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <Input
          label={$t('auth.signup.username_label')}
          name="username"
          type="text"
          autocomplete="username"
          placeholder={$t('auth.signup.username_placeholder')}
          value={form?.username || ''}
          error={form?.errors?.username
            ? translateApiErrorMessage(form.errors.username)
            : undefined}
          variant="modern"
          size="lg"
          rounded="lg"
        >
          {#snippet leftIcon()}
            <Icon icon="ph:at" class="text-gray-400 dark:text-gray-500" />
          {/snippet}
        </Input>

        <Input
          label={$t('auth.signup.email_label')}
          name="email"
          type="email"
          autocomplete="email"
          placeholder={$t('auth.signup.email_placeholder')}
          value={form?.email || ''}
          error={form?.errors?.email
            ? translateApiErrorMessage(form.errors.email)
            : undefined}
          variant="modern"
          size="lg"
          rounded="lg"
        >
          {#snippet leftIcon()}
            <Icon
              icon="ph:envelope-simple"
              class="text-gray-400 dark:text-gray-500"
            />
          {/snippet}
        </Input>
      </div>

      <div class="space-y-2">
        <Input
          label={$t('auth.signup.password_label')}
          name="password"
          type={showPassword ? 'text' : 'password'}
          autocomplete="new-password"
          placeholder={$t('auth.signup.password_placeholder')}
          bind:value={passwordValue}
          error={form?.errors?.password
            ? translateApiErrorMessage(form.errors.password)
            : undefined}
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
            visible={showPassword}
            onclick={() => (showPassword = !showPassword)}
          />
        </Input>

        <PasswordStrength password={passwordValue} />
      </div>

      <Input
        label={$t('auth.signup.confirm_password_label')}
        name="confirm"
        type={showConfirmPassword ? 'text' : 'password'}
        autocomplete="new-password"
        placeholder={$t('auth.signup.confirm_password_placeholder')}
        error={form?.errors?.confirm
          ? translateApiErrorMessage(form.errors.confirm)
          : undefined}
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
          visible={showConfirmPassword}
          onclick={() => (showConfirmPassword = !showConfirmPassword)}
        />
      </Input>

      <Button
        variant="accent"
        size="lg"
        class="w-full mt-2 group"
        type="submit"
        loading={$isLoading}
      >
        {$t('auth.signup.title')}
        {#snippet rightIcon()}
          <Icon
            icon="ph:arrow-right"
            class="w-5 h-5 ml-1 transition-transform duration-200 group-hover:translate-x-1"
          />
        {/snippet}
      </Button>
    </form>
  </div>

  <div class="mt-6">
    <Banner variant="success">
      {#snippet icon()}
        <BannerIcon variant="success" icon="ph:sign-in" />
      {/snippet}
      {#snippet content()}
        <BannerContent
          title={$t('auth.signup.already_have_account_title')}
          description={$t('auth.signup.already_have_account_description')}
        />
      {/snippet}
      {#snippet action()}
        <BannerAction
          variant="success"
          href="/profile/login"
          text={$t('auth.signin')}
        />
      {/snippet}
    </Banner>
  </div>
</div>
