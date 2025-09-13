<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { UserAvatar } from '@slink/feature/User';
  import { Button } from '@slink/ui/components/button';
  import { Input } from '@slink/ui/components/input';

  import { enhance } from '$app/forms';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { useWritable } from '@slink/store/contextAwareStore';

  import { withLoadingState } from '@slink/utils/form/withLoadingState';
  import { toast } from '@slink/utils/ui/toast-sonner.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
    form: any;
  }

  let { data, form }: Props = $props();

  const { user } = data;

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

<div class="min-h-full">
  <div
    class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8"
    in:fade={{ duration: 400 }}
  >
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">
        Profile Settings
      </h1>
      <p class="mt-2 text-slate-600 dark:text-slate-400">
        Manage your account information and security settings
      </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
      <div class="lg:col-span-1">
        <div
          class="rounded-2xl bg-white p-6 shadow-sm dark:bg-slate-800/50 border border-slate-200/50 dark:border-slate-700/50"
        >
          <div class="flex flex-col items-center text-center">
            <div class="mb-4 relative">
              <UserAvatar
                size="xl"
                {user}
                class="ring-4 ring-slate-100 dark:ring-slate-700"
              />
              <button
                class="absolute -bottom-1 -right-1 flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors duration-200"
              >
                <Icon
                  icon="ph:pencil-simple"
                  class="h-4 w-4 text-slate-600 dark:text-slate-300"
                />
              </button>
            </div>

            <h2
              class="text-lg font-semibold text-slate-900 dark:text-slate-100"
            >
              {user.username ?? user.displayName}
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              {user.email}
            </p>
          </div>
        </div>
      </div>

      <div class="lg:col-span-2 space-y-6">
        <div
          class="rounded-2xl bg-white p-6 shadow-sm dark:bg-slate-800/50 border border-slate-200/50 dark:border-slate-700/50"
        >
          <div class="mb-6">
            <h3
              class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2"
            >
              <Icon
                icon="ph:user"
                class="h-5 w-5 text-slate-500 dark:text-slate-400"
              />
              Profile Information
            </h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
              Update your account's profile information
            </p>
          </div>

          <form
            action="?/updateProfile"
            method="POST"
            use:enhance={withLoadingState(isProfileFormLoading)}
            class="space-y-4"
          >
            <Input
              label="Display Name"
              name="display_name"
              error={form?.errors?.display_name}
              value={form?.displayName ?? user.displayName}
              class="w-full"
            >
              {#snippet leftIcon()}
                <Icon icon="ph:user" class="h-4 w-4" />
              {/snippet}
            </Input>

            <div class="flex justify-end pt-4">
              <Button
                variant="outline"
                size="sm"
                type="submit"
                loading={$isProfileFormLoading}
                class="min-w-[120px] bg-slate-900 hover:bg-slate-800 text-white border-slate-900 hover:border-slate-800 dark:bg-slate-100 dark:hover:bg-slate-200 dark:text-slate-900 dark:border-slate-100 dark:hover:border-slate-200"
              >
                {#if $isProfileFormLoading}
                  <Loader
                    variant="simple"
                    size="xs"
                    class="mr-2 !border-white/50 !border-t-white"
                  />
                  Updating...
                {:else}
                  <Icon icon="ph:check" class="h-4 w-4 mr-2" />
                  Save Changes
                {/if}
              </Button>
            </div>
          </form>
        </div>

        <div
          class="rounded-2xl bg-white p-6 shadow-sm dark:bg-slate-800/50 border border-slate-200/50 dark:border-slate-700/50"
        >
          <div class="mb-6">
            <h3
              class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2"
            >
              <Icon
                icon="ph:shield-check"
                class="h-5 w-5 text-slate-500 dark:text-slate-400"
              />
              Security Settings
            </h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
              Update your password to keep your account secure
            </p>
          </div>

          <form
            action="?/changePassword"
            method="POST"
            use:enhance={withLoadingState(isPasswordFormLoading)}
            class="space-y-4"
          >
            <Input
              label="Current Password"
              name="old_password"
              type="password"
              error={form?.errors?.old_password}
              class="w-full"
            >
              {#snippet leftIcon()}
                <Icon icon="ph:lock" class="h-4 w-4" />
              {/snippet}
            </Input>

            <div class="grid gap-4 sm:grid-cols-2">
              <Input
                label="New Password"
                name="password"
                type="password"
                error={form?.errors?.password}
                class="w-full"
              >
                {#snippet leftIcon()}
                  <Icon icon="ph:lock-key" class="h-4 w-4" />
                {/snippet}
              </Input>

              <Input
                label="Confirm New Password"
                name="confirm"
                type="password"
                error={form?.errors?.confirm}
                class="w-full"
              >
                {#snippet leftIcon()}
                  <Icon icon="ph:lock-key" class="h-4 w-4" />
                {/snippet}
              </Input>
            </div>

            <div class="flex justify-end pt-4">
              <Button
                variant="outline"
                size="sm"
                type="submit"
                loading={$isPasswordFormLoading}
                class="min-w-[140px] bg-slate-900 hover:bg-slate-800 text-white border-slate-900 hover:border-slate-800 dark:bg-slate-100 dark:hover:bg-slate-200 dark:text-slate-900 dark:border-slate-100 dark:hover:border-slate-200"
              >
                {#if $isPasswordFormLoading}
                  <Loader
                    variant="simple"
                    size="xs"
                    class="mr-2 !border-white/50 !border-t-white"
                  />
                  Updating...
                {:else}
                  <Icon icon="ph:shield-check" class="h-4 w-4 mr-2" />
                  Update Password
                {/if}
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
