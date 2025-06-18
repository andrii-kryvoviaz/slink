<script lang="ts">
  import type { PageData } from './$types';

  import { enhance } from '$app/forms';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

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

<div class="min-h-full flex items-start justify-center px-4 py-12">
  <div class="w-full max-w-lg">
    <div class="text-center mb-8">
      <div class="flex items-center justify-center mb-6">
        <div
          class="flex items-center justify-center w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 shadow-lg shadow-black/5 dark:shadow-black/20"
        >
          <img class="h-6 w-6" src="/favicon.png" alt="Slink" />
        </div>
      </div>
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
        Create your account
      </h1>
      <p class="text-gray-600 dark:text-gray-400 text-sm">
        Join Slink to start sharing and managing your images
      </p>
    </div>

    <div
      class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl shadow-black/5 dark:shadow-black/20 border border-gray-200/50 dark:border-gray-700/50 p-8"
      in:fade={{ duration: 300, delay: 100 }}
    >
      <form
        class="space-y-6"
        method="POST"
        use:enhance={withLoadingState(isLoading)}
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-4 md:col-span-2">
            <Input
              label="Username"
              name="username"
              type="text"
              autocomplete="username"
              placeholder="Choose a username"
              value={form?.username || ''}
              error={form?.errors?.username}
              size="lg"
              rounded="lg"
              class="transition-all duration-200 border-gray-200/50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/50 focus:bg-white dark:focus:bg-gray-800 focus:border-blue-500/50 focus:ring-blue-500/20"
            >
              {#snippet leftIcon()}
                <Icon icon="ph:user-duotone" class="text-gray-400" />
              {/snippet}
            </Input>
          </div>

          <div class="space-y-4 md:col-span-2">
            <Input
              label="Email Address"
              name="email"
              type="email"
              autocomplete="email"
              placeholder="Enter your email address"
              value={form?.email || ''}
              error={form?.errors?.email}
              size="lg"
              rounded="lg"
              class="transition-all duration-200 border-gray-200/50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/50 focus:bg-white dark:focus:bg-gray-800 focus:border-blue-500/50 focus:ring-blue-500/20"
            >
              {#snippet leftIcon()}
                <Icon icon="ph:envelope-duotone" class="text-gray-400" />
              {/snippet}
            </Input>
          </div>

          <div class="space-y-4">
            <Input
              label="Password"
              name="password"
              type="password"
              autocomplete="new-password"
              placeholder="Create a password"
              error={form?.errors?.password}
              size="lg"
              rounded="lg"
              class="transition-all duration-200 border-gray-200/50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/50 focus:bg-white dark:focus:bg-gray-800 focus:border-blue-500/50 focus:ring-blue-500/20"
            >
              {#snippet leftIcon()}
                <Icon icon="ph:lock-duotone" class="text-gray-400" />
              {/snippet}
            </Input>
          </div>

          <div class="space-y-4">
            <Input
              label="Confirm Password"
              name="confirm"
              type="password"
              autocomplete="new-password"
              placeholder="Confirm your password"
              error={form?.errors?.confirm}
              size="lg"
              rounded="lg"
              class="transition-all duration-200 border-gray-200/50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/50 focus:bg-white dark:focus:bg-gray-800 focus:border-blue-500/50 focus:ring-blue-500/20"
            >
              {#snippet leftIcon()}
                <Icon icon="ph:lock-duotone" class="text-gray-400" />
              {/snippet}
            </Input>
          </div>
        </div>

        <Button
          variant={buttonVariant}
          size="lg"
          class="w-full"
          type="submit"
          loading={$isLoading}
        >
          <span>Create Account</span>
          {#snippet rightIcon()}
            <Icon icon="fa6-solid:chevron-right" />
          {/snippet}
        </Button>
      </form>
    </div>

    <div class="text-center mt-6">
      <p class="text-gray-600 dark:text-gray-400 text-sm">
        Already have an account?
        <a
          href="/profile/login"
          class="font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 ml-1 hover:underline transition-colors duration-200"
        >
          Sign in
        </a>
      </p>
    </div>
  </div>
</div>
