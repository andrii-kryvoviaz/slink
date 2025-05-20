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
  <title>Sign Up | Slink</title>
</svelte:head>

<div
  class="flex grow items-start justify-center p-4 sm:p-12"
  in:fade={{ duration: 200 }}
>
  <div class="flex w-full max-w-3xl items-center lg:w-3/5">
    <div
      class="m-auto mx-auto w-full max-w-xl rounded-lg bg-white p-6 shadow-md dark:bg-gray-800"
    >
      <h2
        class="mt-4 flex gap-3 text-2xl font-light text-gray-700 dark:text-gray-200"
      >
        <img class="h-7 w-auto sm:h-8" src="/favicon.png" alt="" />
        Create an account
      </h2>

      <form
        class="mt-6"
        method="POST"
        use:enhance={withLoadingState(isLoading)}
      >
        <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-2 md:grid-cols-2">
          <Input
            label="Username"
            name="username"
            value={form?.username}
            error={form?.errors.username}
          >
            {#snippet leftIcon()}
              <Icon icon="ph:user" />
            {/snippet}
          </Input>

          <Input
            label="Email"
            name="email"
            type="text"
            value={form?.email}
            error={form?.errors.email}
          >
            {#snippet leftIcon()}
              <Icon icon="mdi-light:email" />
            {/snippet}
          </Input>

          <Input
            label="Password"
            name="password"
            type="password"
            error={form?.errors.password}
          >
            {#snippet leftIcon()}
              <Icon icon="ph:password-light" />
            {/snippet}
          </Input>

          <Input
            label="Confirm Password"
            name="confirm"
            type="password"
            error={form?.errors.confirm}
          >
            {#snippet leftIcon()}
              <Icon icon="ph:password-light" />
            {/snippet}
          </Input>
        </div>
        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 md:pr-6">
          <Button
            variant={buttonVariant}
            size="md"
            class="w-full"
            type="submit"
            loading={$isLoading}
          >
            <span>Sign up</span>
            {#snippet rightIcon()}
              <Icon icon="fa6-solid:chevron-right" />
            {/snippet}
          </Button>
        </div>
      </form>
    </div>
  </div>
</div>
