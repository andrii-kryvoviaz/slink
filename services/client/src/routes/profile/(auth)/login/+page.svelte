<script lang="ts">
  import { untrack } from 'svelte';

  import { enhance } from '$app/forms';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { useWritable } from '@slink/store/contextAwareStore';

  import { settings } from '@slink/lib/settings';

  import { withLoadingState } from '@slink/utils/form/withLoadingState';
  import { toast } from '@slink/utils/ui/toast.svelte';

  import { Button, type ButtonVariant } from '@slink/components/UI/Action';
  import {
    Banner,
    BannerAction,
    BannerContent,
    BannerIcon,
  } from '@slink/components/UI/Banner';
  import { Input } from '@slink/components/UI/Form';

  import type { ActionData, PageData } from './$types';

  interface Props {
    form: ActionData;
    data: PageData;
  }

  let { form, data }: Props = $props();

  let isLoading = useWritable('loginFormLoadingState', false);

  const { isLight } = settings.get('theme', data.settings.theme);

  let buttonVariant: ButtonVariant = $derived($isLight ? 'dark' : 'primary');

  $effect(() => {
    if (!form?.errors) {
      return;
    }

    untrack(() => {
      const errors = form.errors as Record<string, any>;

      if (errors.credentials && typeof errors.credentials === 'string') {
        toast.error(errors.credentials);
      }
    });
  });
</script>

<svelte:head>
  <title>Sign In | Slink</title>
</svelte:head>

<div
  class="w-full max-w-md mx-auto px-6 py-8"
  in:fly={{ y: 20, duration: 500, delay: 100 }}
>
  <div class="flex items-center justify-start gap-4 mb-6">
    <div
      class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary/10 to-primary/5 border border-primary/10 hover:border-primary/20 hover:scale-105 transition-all duration-200 cursor-pointer flex items-center justify-center shadow-sm"
    >
      <img class="h-5 w-5" src="/favicon.png" alt="Slink" />
    </div>
    <div class="text-left">
      <h1
        class="text-2xl font-semibold text-gray-900 dark:text-white tracking-tight"
      >
        Welcome back
      </h1>
      <p class="text-gray-600 dark:text-gray-400 text-sm">
        Sign in to continue to Slink
      </p>
    </div>
  </div>

  <div
    class="bg-white/50 dark:bg-gray-900/30 backdrop-blur-sm rounded-2xl border border-gray-200/50 dark:border-gray-700/30 p-6 mb-6 shadow-sm"
  >
    <form
      class="space-y-4"
      method="POST"
      use:enhance={withLoadingState(isLoading)}
      in:fade={{ duration: 400, delay: 200 }}
    >
      <div class="space-y-3">
        <div>
          <Input
            label="Email or Username"
            name="username"
            type="text"
            autocomplete="username"
            placeholder="Enter email or username"
            value={form?.username || ''}
            error={typeof form?.errors === 'object' && 'username' in form.errors
              ? form.errors.username
              : undefined}
            variant="modern"
            size="md"
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

        <div>
          <Input
            label="Password"
            name="password"
            type="password"
            autocomplete="current-password"
            placeholder="Enter password"
            error={typeof form?.errors === 'object' && 'password' in form.errors
              ? form.errors.password
              : undefined}
            variant="modern"
            size="md"
            rounded="lg"
          >
            {#snippet leftIcon()}
              <Icon
                icon="ph:lock-simple"
                class="text-gray-400 dark:text-gray-500"
              />
            {/snippet}
          </Input>
        </div>
      </div>

      <Button
        variant={buttonVariant}
        size="md"
        class="w-full mt-5"
        type="submit"
        loading={$isLoading}
      >
        Sign In
        {#snippet rightIcon()}
          <Icon icon="ph:arrow-right" class="ml-2" />
        {/snippet}
      </Button>
    </form>
  </div>

  {#if data.globalSettings?.user?.allowRegistration}
    <Banner variant="info">
      {#snippet icon()}
        <BannerIcon variant="info" icon="ph:user-plus" />
      {/snippet}
      {#snippet content()}
        <BannerContent
          title="Need an account?"
          description="Create one to start sharing images"
        />
      {/snippet}
      {#snippet action()}
        <BannerAction variant="info" href="/profile/signup" text="Sign Up" />
      {/snippet}
    </Banner>
  {/if}
</div>
