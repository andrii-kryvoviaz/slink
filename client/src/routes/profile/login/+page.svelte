<script lang="ts">
  import Icon from '@iconify/svelte';

  import { isLightTheme } from '@slink/store/settings.js';

  import { Button, type ButtonVariant } from '@slink/components/Common';
  import { Input } from '@slink/components/Form';

  let buttonVariant: ButtonVariant = 'primary';
  $: buttonVariant = $isLightTheme ? 'dark' : 'primary';
</script>

<div class="flex flex-grow items-center justify-center">
  <div
    class="m-auto mx-auto w-full max-w-sm rounded-lg bg-white p-6 shadow-md dark:bg-gray-800"
  >
    <div class="mx-auto flex justify-center">
      <img class="h-7 w-auto sm:h-8" src="/favicon.png" alt="" />
    </div>

    <h2 class="mt-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
      Welcome Back
    </h2>

    <form class="mt-6">
      <div class="flex flex-col gap-4">
        <div>
          <Input label="Username" name="username">
            <Icon icon="ph:user" slot="leftIcon" />
          </Input>
        </div>

        <div>
          <Input label="Password" name="password" type="password">
            <Icon icon="ph:password-light" slot="leftIcon" />
            <a
              slot="topRightText"
              href="/profile/forget"
              class="text-xs text-gray-600 hover:underline dark:text-gray-400"
              >Forget Password?</a
            >
          </Input>
        </div>
      </div>

      <div class="mt-6">
        <Button variant={buttonVariant} size="md" class="w-full">
          Sign In
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
