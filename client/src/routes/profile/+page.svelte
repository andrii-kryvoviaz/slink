<script lang="ts">
  import { settings } from '@slink/lib/settings';

  import { enhance } from '$app/forms';
  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';

  import { useWritable } from '@slink/store/contextAwareStore';

  import { withLoadingState } from '@slink/utils/form/withLoadingState';
  import { toast } from '@slink/utils/ui/toast';

  import { Button, type ButtonVariant } from '@slink/components/Common';
  import { Input } from '@slink/components/Form';
  import { UserAvatar } from '@slink/components/User';

  import type { PageServerData } from './$types';

  export let data: PageServerData;
  export let form;

  const { user } = data;

  let isPasswordFormLoading = useWritable(
    'changePasswordFormLoadingState',
    false
  );
  let isProfileFormLoading = useWritable(
    'updateProfileFormLoadingState',
    false
  );

  let buttonVariant: ButtonVariant = 'primary';

  const { isLight } = settings.get('theme', data.settings.theme);

  $: buttonVariant = $isLight ? 'dark' : 'primary';

  $: if (form?.passwordWasChanged) {
    toast.success('Password changed successfully');
  }

  $: if (form?.profileWasUpdated) {
    toast.success('Profile updated successfully');
  }

  $: if (form?.errors?.message) {
    toast.error(form.errors.message);
  }
</script>

<svelte:head>
  <title>{user.displayName} | Slink</title>
</svelte:head>

<div class="container mx-auto px-6 py-8">
  <div class="flex flex-col justify-evenly gap-6 md:flex-row">
    <div class="flex">
      <UserAvatar size="lg" {user} />
      <div class="ml-4">
        <h1 class="text-2xl font-bold">{user.username ?? user.displayName}</h1>
        <p class="opacity-60">{user.email}</p>
      </div>
    </div>
    <div class="max-w-md flex-grow">
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
              <Icon icon="ph:user" slot="leftIcon" />
            </Input>

            <Button
              variant={buttonVariant}
              size="md"
              class="mt-2 w-full"
              loading={$isProfileFormLoading}
            >
              <span>Update Profile</span>
              <Icon icon="fluent:chevron-right-48-regular" slot="rightIcon" />
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
              <Icon icon="carbon:password" slot="leftIcon" />
            </Input>
          </div>

          <div>
            <Input
              label="New Password"
              name="password"
              type="password"
              error={form?.errors?.password}
            >
              <Icon icon="ph:password-light" slot="leftIcon" />
            </Input>
          </div>

          <div>
            <Input
              label="Confirm New Password"
              name="confirm"
              type="password"
              error={form?.errors?.confirm}
            >
              <Icon icon="ph:password-light" slot="leftIcon" />
            </Input>
          </div>
        </div>

        <Button
          variant={buttonVariant}
          size="md"
          class="mt-2 w-full"
          loading={$isPasswordFormLoading}
        >
          <span>Change Password</span>
          <Icon icon="fluent:chevron-right-48-regular" slot="rightIcon" />
        </Button>
      </form>
    </div>
  </div>
</div>
