<script lang="ts">
  import { PasswordToggle, SsoProviderButton } from '@slink/feature/Auth';
  import {
    Banner,
    BannerAction,
    BannerContent,
    BannerFooter,
    BannerIcon,
  } from '@slink/feature/Layout';
  import { Button } from '@slink/ui/components/button';
  import { Input } from '@slink/ui/components/input';

  import { enhance } from '$app/forms';
  import { t } from '$lib/i18n';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { OAuthProviderConfig } from '@slink/lib/auth/oauth';

  import { withLoadingState } from '@slink/utils/form/withLoadingState';
  import { useWritable } from '@slink/utils/store/contextAwareStore';
  import { toast } from '@slink/utils/ui/toast-sonner.svelte';

  import type { ActionData, PageData } from './$types';

  interface Props {
    form: ActionData;
    data: PageData;
  }

  let { form, data }: Props = $props();

  let isLoading = useWritable('loginFormLoadingState', false);
  let usernameValue = $state('');
  let passwordValue = $state('');

  $effect(() => {
    if (form?.username) {
      usernameValue = form.username;
    }
  });
  let showPassword = $state(false);
  let formElement: HTMLFormElement;

  let providers = $derived(
    (data.ssoProviders ?? []).map((p) => ({
      ...OAuthProviderConfig.resolve(p.slug),
      name: p.name,
    })),
  );

  function fillDemoCredentials() {
    if (
      data.globalSettings?.demo?.demoUsername &&
      data.globalSettings?.demo?.demoPassword
    ) {
      usernameValue = data.globalSettings.demo.demoUsername;
      passwordValue = data.globalSettings.demo.demoPassword;

      setTimeout(() => {
        formElement?.requestSubmit();
      }, 100);
    }
  }

  $effect(() => {
    if (!form?.errors) return;

    const errors = form.errors as Record<string, any>;
    if (errors.credentials && typeof errors.credentials === 'string') {
      toast.error(errors.credentials);
    }
  });
</script>

<svelte:head>
  <title>{$t('auth.login.page_title')}</title>
</svelte:head>

<div
  class="w-full max-w-md mx-auto px-6 py-8"
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
        {$t('auth.login.title')}
      </h1>
      <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">
        {$t('auth.login.subtitle')}
      </p>
    </div>
  </div>

  <div
    class="bg-white/60 dark:bg-gray-900/40 backdrop-blur-sm rounded-2xl border border-gray-200/60 dark:border-gray-700/40 p-6 shadow-sm"
  >
    <form
      bind:this={formElement}
      class="space-y-5"
      method="POST"
      use:enhance={withLoadingState(isLoading)}
      in:fade={{ duration: 400, delay: 200 }}
    >
      <Input
        label={$t('auth.login.username_label')}
        name="username"
        type="text"
        autocomplete="username"
        placeholder={$t('auth.login.username_placeholder')}
        bind:value={usernameValue}
        error={typeof form?.errors === 'object' && 'username' in form.errors
          ? form.errors.username
          : undefined}
        variant="modern"
        size="lg"
        rounded="lg"
      >
        {#snippet leftIcon()}
          <Icon icon="ph:user" class="text-gray-400 dark:text-gray-500" />
        {/snippet}
      </Input>

      <div class="space-y-2">
        <Input
          label={$t('auth.login.password_label')}
          name="password"
          type={showPassword ? 'text' : 'password'}
          autocomplete="current-password"
          placeholder={$t('auth.login.password_placeholder')}
          bind:value={passwordValue}
          error={typeof form?.errors === 'object' && 'password' in form.errors
            ? form.errors.password
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
      </div>

      <Button
        variant="accent"
        size="lg"
        class="w-full mt-2 group"
        type="submit"
        loading={$isLoading}
      >
        {$t('auth.signin')}
        {#snippet rightIcon()}
          <Icon
            icon="ph:arrow-right"
            class="w-5 h-5 ml-1 transition-transform duration-200 group-hover:translate-x-1"
          />
        {/snippet}
      </Button>
    </form>

    {#if providers.length > 0}
      <div class="mt-5">
        <div class="relative flex items-center">
          <div
            class="flex-grow border-t border-gray-200/60 dark:border-gray-700/40"
          ></div>
          <span
            class="mx-4 shrink-0 text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wide"
            >{$t('auth.login.or_continue_with')}</span
          >
          <div
            class="flex-grow border-t border-gray-200/60 dark:border-gray-700/40"
          ></div>
        </div>

        <div class="mt-4 space-y-3">
          {#each providers as provider (provider.slug)}
            <SsoProviderButton {provider} />
          {/each}
        </div>
      </div>
    {/if}
  </div>

  <div class="mt-6 space-y-4">
    {#if data.globalSettings?.demo?.enabled}
      <Banner variant="violet">
        {#snippet icon()}
          <BannerIcon variant="violet" icon="ph:flask" />
        {/snippet}
        {#snippet content()}
          <BannerContent
            title={$t('auth.login.demo_mode_title')}
            description={$t('auth.login.demo_mode_description')}
          />
        {/snippet}
        {#snippet action()}
          <BannerAction
            variant="violet"
            text={$t('auth.login.get_in')}
            icon="ph:sign-in"
            onclick={fillDemoCredentials}
          />
        {/snippet}
        {#snippet footer()}
          <BannerFooter
            variant="violet"
            icon="ph:cursor-click"
            text={$t('auth.login.demo_mode_footer')}
          />
        {/snippet}
      </Banner>
    {/if}

    {#if data.globalSettings?.user?.allowRegistration}
      <Banner variant="info">
        {#snippet icon()}
          <BannerIcon variant="info" icon="ph:user-plus" />
        {/snippet}
        {#snippet content()}
          <BannerContent
            title={$t('auth.login.need_account_title')}
            description={$t('auth.login.need_account_description')}
          />
        {/snippet}
        {#snippet action()}
          <BannerAction
            variant="info"
            href="/profile/signup"
            text={$t('auth.signup.cta')}
          />
        {/snippet}
      </Banner>
    {/if}
  </div>
</div>
