<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { SettingItem } from '@slink/feature/Settings';
  import { Subtitle, Title } from '@slink/feature/Text';
  import { UserAvatar } from '@slink/feature/User';
  import { Button } from '@slink/ui/components/button';
  import { Input } from '@slink/ui/components/input';

  import { enhance } from '$app/forms';
  import { t } from '$lib/i18n';
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
      toast.success($t('profile.toast.password_changed'));
    }
  });

  $effect(() => {
    if (form?.profileWasUpdated) {
      toast.success($t('profile.toast.profile_updated'));
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
    <Title size="sm">{$t('profile.title')}</Title>
    <Subtitle>{$t('profile.subtitle')}</Subtitle>
  </header>

  <div class="space-y-8">
    <section class="space-y-1">
      <div class="flex items-center justify-between gap-4 pb-3">
        <h2
          class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
        >
          {$t('profile.sections.profile_information')}
        </h2>
      </div>

      <div
        class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 overflow-hidden"
      >
        <div class="flex items-center gap-4 px-4 py-4">
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

        <form
          id="profile-form"
          action="?/updateProfile"
          method="POST"
          use:enhance={withLoadingState(isProfileFormLoading)}
        >
          <SettingItem>
            {#snippet label()}
              {$t('profile.display_name.label')}
            {/snippet}
            {#snippet hint()}
              {$t('profile.display_name.hint')}
            {/snippet}
            <Input
              name="display_name"
              error={form?.errors?.display_name}
              value={form?.displayName ?? user.displayName}
              class="w-full max-w-md"
            />
          </SettingItem>
        </form>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4">
        {#if $isProfileFormLoading}
          <div
            class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
          >
            <Loader variant="minimal" size="xs" />
            <span>{$t('settings.common.saving')}</span>
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
          {$t('settings.common.save_changes')}
        </Button>
      </div>
    </section>

    <section class="space-y-1">
      <div class="flex items-center justify-between gap-4 pb-3">
        <h2
          class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
        >
          {$t('profile.sections.security')}
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
          <SettingItem>
            {#snippet label()}
              {$t('profile.password.current.label')}
            {/snippet}
            {#snippet hint()}
              {$t('profile.password.current.hint')}
            {/snippet}
            <Input
              name="old_password"
              type="password"
              placeholder={$t('profile.password.current.placeholder')}
              error={form?.errors?.old_password}
              class="w-full max-w-md"
            />
          </SettingItem>

          <SettingItem>
            {#snippet label()}
              {$t('profile.password.new.label')}
            {/snippet}
            {#snippet hint()}
              {$t('profile.password.new.hint')}
            {/snippet}
            <Input
              name="password"
              type="password"
              placeholder={$t('profile.password.new.placeholder')}
              error={form?.errors?.password}
              class="w-full max-w-md"
            />
          </SettingItem>

          <SettingItem>
            {#snippet label()}
              {$t('profile.password.confirm.label')}
            {/snippet}
            {#snippet hint()}
              {$t('profile.password.confirm.hint')}
            {/snippet}
            <Input
              name="confirm"
              type="password"
              placeholder={$t('profile.password.confirm.placeholder')}
              error={form?.errors?.confirm}
              class="w-full max-w-md"
            />
          </SettingItem>
        </form>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4">
        {#if $isPasswordFormLoading}
          <div
            class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
          >
            <Loader variant="minimal" size="xs" />
            <span>{$t('profile.password.updating')}</span>
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
          {$t('profile.password.update')}
        </Button>
      </div>
    </section>
  </div>
</div>
