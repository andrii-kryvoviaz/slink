<script lang="ts">
  import type { PageData } from './$types';

  import { enhance } from '$app/forms';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { useWritable } from '@slink/store/contextAwareStore';

  import { settings } from '@slink/lib/settings';

  import { withLoadingState } from '@slink/utils/form/withLoadingState';
  import { toast } from '@slink/utils/ui/toast';

  import { Button, type ButtonVariant } from '@slink/components/UI/Action';
  import { Input } from '@slink/components/UI/Form';

  interface Props {
    form: any;
    data: PageData;
  }

  let { form, data }: Props = $props();

  let isLoading = useWritable('signUpFormLoadingState', false);

  const { isLight } = settings.get('theme', data.settings.theme);

  let buttonVariant: ButtonVariant = $derived($isLight ? 'dark' : 'primary');

  $effect(() => {
    if (form?.errors.message) {
      toast.error(form.errors.message);
    }
  });
</script>

<svelte:head>
  <title>Create Account | Slink</title>
</svelte:head>

<div
  class="w-full max-w-lg mx-auto px-6 py-8"
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
        Create Account
      </h1>
      <p class="text-gray-600 dark:text-gray-400 text-sm">
        Join Slink to start sharing your images
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
            label="Username"
            name="username"
            type="text"
            autocomplete="username"
            placeholder="Choose a username"
            value={form?.username || ''}
            error={form?.errors?.username}
            size="md"
            rounded="lg"
            class="border-0 bg-gray-50/80 dark:bg-gray-800/50 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-blue-500/20 hover:bg-gray-100/50 dark:hover:bg-gray-800/70 transition-all duration-200"
          >
            {#snippet leftIcon()}
              <Icon
                icon="ph:user-circle"
                class="text-gray-400 dark:text-gray-500"
              />
            {/snippet}
          </Input>
        </div>

        <div>
          <Input
            label="Email Address"
            name="email"
            type="email"
            autocomplete="email"
            placeholder="Enter your email"
            value={form?.email || ''}
            error={form?.errors?.email}
            size="md"
            rounded="lg"
            class="border-0 bg-gray-50/80 dark:bg-gray-800/50 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-blue-500/20 hover:bg-gray-100/50 dark:hover:bg-gray-800/70 transition-all duration-200"
          >
            {#snippet leftIcon()}
              <Icon
                icon="ph:envelope-simple"
                class="text-gray-400 dark:text-gray-500"
              />
            {/snippet}
          </Input>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <Input
              label="Password"
              name="password"
              type="password"
              autocomplete="new-password"
              placeholder="Create password"
              error={form?.errors?.password}
              size="md"
              rounded="lg"
              class="border-0 bg-gray-50/80 dark:bg-gray-800/50 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-blue-500/20 hover:bg-gray-100/50 dark:hover:bg-gray-800/70 transition-all duration-200"
            >
              {#snippet leftIcon()}
                <Icon
                  icon="ph:lock-simple"
                  class="text-gray-400 dark:text-gray-500"
                />
              {/snippet}
            </Input>
          </div>

          <div>
            <Input
              label="Confirm"
              name="confirm"
              type="password"
              autocomplete="new-password"
              placeholder="Confirm password"
              error={form?.errors?.confirm}
              size="md"
              rounded="lg"
              class="border-0 bg-gray-50/80 dark:bg-gray-800/50 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-blue-500/20 hover:bg-gray-100/50 dark:hover:bg-gray-800/70 transition-all duration-200"
            >
              {#snippet leftIcon()}
                <Icon
                  icon="ph:check-circle"
                  class="text-gray-400 dark:text-gray-500"
                />
              {/snippet}
            </Input>
          </div>
        </div>
      </div>

      <Button
        variant={buttonVariant}
        size="md"
        class="w-full mt-5"
        type="submit"
        loading={$isLoading}
      >
        Create Account
        {#snippet rightIcon()}
          <Icon icon="ph:arrow-right" class="ml-2" />
        {/snippet}
      </Button>
    </form>
  </div>

  <div
    class="p-4 rounded-xl bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 border border-slate-200/50 dark:border-slate-700/50 shadow-sm"
  >
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div
          class="flex items-center justify-center w-10 h-10 rounded-lg bg-slate-900 dark:bg-white shadow-sm"
        >
          <Icon
            icon="ph:sign-in"
            class="h-5 w-5 text-white dark:text-slate-900"
          />
        </div>
        <div>
          <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
            Already have an account?
          </h3>
          <p class="text-xs text-slate-600 dark:text-slate-400">
            Sign in to access your images
          </p>
        </div>
      </div>
      <Button href="/profile/login" variant="glass" size="sm" rounded="full">
        Sign In
        <Icon icon="ph:arrow-right" class="h-3 w-3 ml-1" />
      </Button>
    </div>
  </div>
</div>
