<script lang="ts">
  import { Button, type ButtonVariant } from '@slink/components/Common';
  import { isLightTheme } from '@slink/store/settings.js';
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

    <form class="mt-6">
      <div class="flex flex-col gap-4">
        <div>
          <Input label="Username" name="username" />
        </div>

        <div>
          <Input label="Password" name="password" type="password">
            <a
              slot="postfix"
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
