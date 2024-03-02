<script lang="ts">
  import { settings } from '@slink/lib/settings';

  import { enhance } from '$app/forms';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { useWritable } from '@slink/store/contextAwareStore';

  import { withLoadingState } from '@slink/utils/form/withLoadingState';
  import { toast } from '@slink/utils/ui/toast';

  import { Button, type ButtonVariant } from '@slink/components/Common';
  import { Input } from '@slink/components/Form';

  import type { PageData } from './$types';

  export let form;
  export let data: PageData;

  let isLoading = useWritable('signUpFormLoadingState', false);
  let buttonVariant: ButtonVariant = 'primary';

  const { isLight } = settings.get('theme', data.settings.theme);
  $: buttonVariant = $isLight ? 'dark' : 'primary';
  $: if (form?.errors.message) {
    toast.error(form.errors.message);
  }
</script>

<div
  class="flex flex-grow items-center justify-center"
  in:fade={{ duration: 200 }}
>
  <div class="mx-auto flex w-full max-w-3xl items-center p-8 lg:w-3/5 lg:px-12">
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
            label="Display Name"
            name="display_name"
            value={form?.display_name}
            error={form?.errors.display_name}
          >
            <Icon icon="ph:user" slot="leftIcon" />
          </Input>

          <Input
            label="Email"
            name="email"
            type="text"
            value={form?.email}
            error={form?.errors.email}
          >
            <Icon icon="mdi-light:email" slot="leftIcon" />
          </Input>

          <Input
            label="Password"
            name="password"
            type="password"
            error={form?.errors.password}
          >
            <Icon icon="ph:password-light" slot="leftIcon" />
          </Input>

          <Input
            label="Confirm Password"
            name="confirm"
            type="password"
            error={form?.errors.confirm}
          >
            <Icon icon="ph:password-light" slot="leftIcon" />
          </Input>
        </div>
        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 md:pr-6">
          <Button
            variant={buttonVariant}
            size="md"
            class="w-full"
            loading={$isLoading}
          >
            <span>Sign up</span>
            <Icon icon="fa6-solid:chevron-right" slot="rightIcon" />
          </Button>
        </div>
      </form>
    </div>
  </div>
</div>
