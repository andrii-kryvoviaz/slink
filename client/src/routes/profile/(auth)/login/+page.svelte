<script lang="ts">
  import { settings } from '@slink/lib/settings';

  import { enhance } from '$app/forms';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { useWritable } from '@slink/store/contextAwareStore';

  import { withLoadingState } from '@slink/utils/form/withLoadingState';
  import { toast } from '@slink/utils/ui/toast';

  import { Button, type ButtonVariant } from '@slink/components/UI/Action';
  import { Input } from '@slink/components/UI/Form';

  import type { ActionData, PageData } from './$types';

  export let form: ActionData;
  export let data: PageData;

  let isLoading = useWritable('loginFormLoadingState', false);
  let buttonVariant: ButtonVariant = 'primary';

  const { isLight } = settings.get('theme', data.settings.theme);

  $: buttonVariant = $isLight ? 'dark' : 'primary';
  $: if (form?.errors.message) {
    toast.error(form.errors.message);
  }
</script>

<svelte:head>
  <title>Login | Slink</title>
</svelte:head>

<div
  class="flex flex-grow items-start justify-center p-4 sm:p-12"
  in:fade={{ duration: 200 }}
>
  <div
    class="w-full max-w-sm rounded-lg bg-white p-6 shadow-md dark:bg-gray-800"
  >
    <h2
      class="mt-4 flex gap-3 text-2xl font-light text-gray-700 dark:text-gray-200"
    >
      <img class="h-7 w-auto sm:h-8" src="/favicon.png" alt="" />
      Welcome Back
    </h2>

    <form class="mt-6" method="POST" use:enhance={withLoadingState(isLoading)}>
      <div class="flex flex-col gap-2">
        <div>
          <Input
            label="Username or Email"
            name="username"
            value={form?.username || ''}
            error={form?.errors.username ||
              form?.errors.email ||
              !!form?.errors.credentials}
          >
            <Icon icon="ph:user" slot="leftIcon" />
          </Input>
        </div>

        <div>
          <Input
            label="Password"
            name="password"
            type="password"
            error={form?.errors.password || form?.errors.credentials}
          >
            <Icon icon="ph:password-light" slot="leftIcon" />
          </Input>
          <!-- ToDo: Implement forget password feature
            <a
              slot="topRightText"
              href="/profile/forget"
              class="text-xs text-gray-600 hover:underline dark:text-gray-400"
              >Forget Password?</a
            >-->
        </div>
      </div>

      <div class="mt-6">
        <Button
          variant={buttonVariant}
          size="md"
          class="w-full"
          type="submit"
          loading={$isLoading}
        >
          <span>Sign In</span>
          <Icon icon="fluent:chevron-right-48-regular" slot="rightIcon" />
        </Button>
      </div>
    </form>

    <p class="mt-8 text-center text-xs font-light text-gray-400">
      Don't have an account? <a
        href="/profile/signup"
        class="font-medium text-gray-700 hover:underline dark:text-gray-200"
        >Create One</a
      >
    </p>
  </div>
</div>
