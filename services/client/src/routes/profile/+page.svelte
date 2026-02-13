<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { UserAvatar } from '@slink/feature/User';
  import { Button } from '@slink/ui/components/button';
  import { Input } from '@slink/ui/components/input';

  import { enhance } from '$app/forms';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { withLoadingState } from '@slink/utils/form/withLoadingState';
  import { useWritable } from '@slink/utils/store/contextAwareStore';
  import { toast } from '@slink/utils/ui/toast-sonner.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
    form: any;
  }

  let { data, form }: Props = $props();

  let user = $derived(data.user);

  let isPasswordFormLoading = useWritable(
    'changePasswordFormLoadingState',
    false,
  );
  let isProfileFormLoading = useWritable(
    'updateProfileFormLoadingState',
    false,
  );

  $effect(() => {
    if (form?.passwordWasChanged) {
      toast.success('Password changed successfully');
    }
  });

  $effect(() => {
    if (form?.profileWasUpdated) {
      toast.success('Profile updated successfully');
    }
  });

  $effect(() => {
    if (form?.errors?.message) {
      toast.error(form.errors.message);
    }
  });
</script>

<svelte:head>
  <title>{user.displayName} | Slink</title>
</svelte:head>

<div
  class="flex flex-col w-full max-w-2xl px-6 py-8"
  in:fade={{ duration: 150 }}
>
  <header class="mb-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
      Profile Settings
    </h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      Manage your account information and security settings
    </p>
  </header>

  <div class="space-y-8">
    <section class="space-y-1">
      <div class="flex items-center justify-between gap-4 pb-3">
        <h2
          class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
        >
          Profile Information
        </h2>
      </div>

      <div
        class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 overflow-hidden"
      >
        <form
          id="profile-form"
          action="?/updateProfile"
          method="POST"
          use:enhance={withLoadingState(isProfileFormLoading)}
        >
          <div class="px-4 py-4">
            <div class="flex flex-col gap-3">
              <div class="flex items-center gap-4 mb-2">
                <UserAvatar size="lg" {user} />
                <div class="flex-1 min-w-0">
                  <p
                    class="text-sm font-medium text-gray-900 dark:text-white truncate"
                  >
                    {user.username ?? user.displayName}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {user.email}
                  </p>
                </div>
              </div>
              <div>
                <label
                  for="display_name"
                  class="block text-sm font-medium text-gray-900 dark:text-white"
                >
                  Display Name
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  This is how your name will appear across the platform
                </p>
              </div>
              <Input
                name="display_name"
                error={form?.errors?.display_name}
                value={form?.displayName ?? user.displayName}
                class="w-full max-w-md"
              >
                {#snippet leftIcon()}
                  <Icon icon="ph:user" class="h-4 w-4" />
                {/snippet}
              </Input>
            </div>
          </div>
        </form>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4">
        {#if $isProfileFormLoading}
          <div
            class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
          >
            <Loader variant="minimal" size="xs" />
            <span>Saving...</span>
          </div>
        {/if}

        <Button
          type="submit"
          form="profile-form"
          variant="soft-blue"
          rounded="full"
          size="sm"
          disabled={$isProfileFormLoading}
        >
          Save Changes
        </Button>
      </div>
    </section>

    <section class="space-y-1">
      <div class="flex items-center justify-between gap-4 pb-3">
        <h2
          class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
        >
          Security
        </h2>
      </div>

      <div
        class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 overflow-hidden"
      >
        <form
          id="password-form"
          action="?/changePassword"
          method="POST"
          use:enhance={withLoadingState(isPasswordFormLoading)}
        >
          <div class="px-4 py-4">
            <div class="flex flex-col gap-3">
              <div>
                <label
                  for="old_password"
                  class="block text-sm font-medium text-gray-900 dark:text-white"
                >
                  Change Password
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Update your password to keep your account secure
                </p>
              </div>

              <Input
                name="old_password"
                type="password"
                placeholder="Current password"
                error={form?.errors?.old_password}
                class="w-full max-w-md"
              >
                {#snippet leftIcon()}
                  <Icon icon="ph:lock" class="h-4 w-4" />
                {/snippet}
              </Input>

              <div class="grid gap-3 sm:grid-cols-2 max-w-md">
                <Input
                  name="password"
                  type="password"
                  placeholder="New password"
                  error={form?.errors?.password}
                  class="w-full"
                >
                  {#snippet leftIcon()}
                    <Icon icon="ph:lock-key" class="h-4 w-4" />
                  {/snippet}
                </Input>

                <Input
                  name="confirm"
                  type="password"
                  placeholder="Confirm password"
                  error={form?.errors?.confirm}
                  class="w-full"
                >
                  {#snippet leftIcon()}
                    <Icon icon="ph:lock-key" class="h-4 w-4" />
                  {/snippet}
                </Input>
              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4">
        {#if $isPasswordFormLoading}
          <div
            class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
          >
            <Loader variant="minimal" size="xs" />
            <span>Updating...</span>
          </div>
        {/if}

        <Button
          type="submit"
          form="password-form"
          variant="soft-blue"
          rounded="full"
          size="sm"
          disabled={$isPasswordFormLoading}
        >
          Update Password
        </Button>
      </div>
    </section>
  </div>
</div>
