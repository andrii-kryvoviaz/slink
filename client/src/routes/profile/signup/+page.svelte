<script lang="ts">
  import Icon from '@iconify/svelte';

  import { isLightTheme } from '@slink/store/settings.js';

  import { Button, type ButtonVariant } from '@slink/components/Common';
  import { Input } from '@slink/components/Form';

  let buttonVariant: ButtonVariant = 'primary';
  $: buttonVariant = $isLightTheme ? 'dark' : 'primary';
</script>

<div class="flex flex-grow items-center justify-center">
  <div class="mx-auto flex w-full max-w-3xl items-center p-8 lg:w-3/5 lg:px-12">
    <div
      class="m-auto mx-auto w-full max-w-sm rounded-lg bg-white p-6 shadow-md dark:bg-gray-800"
    >
      <div class="mx-auto flex justify-center">
        <img class="h-7 w-auto sm:h-8" src="/favicon.png" alt="" />
      </div>

      <form class="mt-6">
        <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-1">
          <Input label="Display Name" name="display_name">
            <Icon icon="ph:user" slot="leftIcon" />
          </Input>

          <Input label="Email" name="email" type="text">
            <Icon icon="mdi-light:email" slot="leftIcon" />
          </Input>

          <Input label="Password" name="password" type="password">
            <Icon icon="ph:password-light" slot="leftIcon" />
          </Input>

          <Input
            label="Confirm Password"
            name="confirm_password"
            type="password"
          >
            <Icon icon="ph:password-light" slot="leftIcon" />
            <span slot="error">Passwords do not match </span>
          </Input>
        </div>
        <div class="mt-6">
          <Button variant={buttonVariant} size="md" class="w-full" loading>
            <span>Sign up</span>
            <Icon icon="fa6-solid:chevron-right" slot="rightIcon" />
          </Button>
        </div>
      </form>
    </div>
  </div>
</div>
