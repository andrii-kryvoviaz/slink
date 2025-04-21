<script lang="ts">
  import type { PageServerData } from './$types';

  import { enhance } from '$app/forms';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { useWritable } from '@slink/store/contextAwareStore';

  import { settings } from '@slink/lib/settings';

  import { withLoadingState } from '@slink/utils/form/withLoadingState';
  import { toast } from '@slink/utils/ui/toast';

  import { UserAvatar } from '@slink/components/Feature/User';
  import { Button, type ButtonVariant } from '@slink/components/UI/Action';
  import { Input } from '@slink/components/UI/Form';

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

  const { isLight } = settings.get('theme', data.settings.theme);

  let buttonVariant: ButtonVariant = $derived($isLight ? 'dark' : 'primary');

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

<div class="container mx-auto px-6 py-6 sm:py-10" in:fade={{ duration: 300 }}>
  <div class="flex flex-col justify-evenly gap-6 md:flex-row">
    <div class="flex">
      <UserAvatar size="lg" {user} />
      <div class="ml-4">
        <h1 class="text-2xl font-bold">{user.username ?? user.displayName}</h1>
        <p class="opacity-60">{user.email}</p>
      </div>
    </div>
    <div class="max-w-md grow">
      <form
        action="?/updateProfile"
        method="POST"
        use:enhance={withLoadingState(isProfileFormLoading)}
      >
        <div class="flex flex-col gap-2">
          <div>
            <Input
              label="Display Name"
              name="display_name"
              error={form?.errors?.display_name}
              value={form?.displayName ?? user.displayName}
            >
              {#snippet leftIcon()}
                <Icon icon="ph:user" />
              {/snippet}
            </Input>

            <Button
              variant={buttonVariant}
              size="md"
              class="mt-2 w-full"
              type="submit"
              loading={$isProfileFormLoading}
            >
              <span>Update Profile</span>
              {#snippet rightIcon()}
                <Icon icon="fluent:chevron-right-48-regular" />
              {/snippet}
            </Button>
          </div>
        </div>
      </form>
      <hr class="my-6 opacity-20" />
      <form
        action="?/changePassword"
        method="POST"
        use:enhance={withLoadingState(isPasswordFormLoading)}
      >
        <div class="flex flex-col gap-2">
          <div>
            <Input
              label="Current Password"
              name="old_password"
              type="password"
              error={form?.errors?.old_password}
            >
              {#snippet leftIcon()}
                <Icon icon="carbon:password" />
              {/snippet}
            </Input>
          </div>

          <div>
            <Input
              label="New Password"
              name="password"
              type="password"
              error={form?.errors?.password}
            >
              {#snippet leftIcon()}
                <Icon icon="ph:password-light" />
              {/snippet}
            </Input>
          </div>

          <div>
            <Input
              label="Confirm New Password"
              name="confirm"
              type="password"
              error={form?.errors?.confirm}
            >
              {#snippet leftIcon()}
                <Icon icon="ph:password-light" />
              {/snippet}
            </Input>
          </div>
        </div>

        <Button
          variant={buttonVariant}
          size="md"
          class="mt-2 w-full"
          type="submit"
          loading={$isPasswordFormLoading}
        >
          <span>Change Password</span>
          {#snippet rightIcon()}
            <Icon icon="fluent:chevron-right-48-regular" />
          {/snippet}
        </Button>
      </form>
    </div>
  </div>
</div>
