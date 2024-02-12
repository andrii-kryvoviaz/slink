<script lang="ts">
  import { Button, type ButtonVariant } from '@slink/components/Common';
  import { isLightTheme } from '@slink/store/settings.js';
  import { Input } from '@slink/components/Form';

  let buttonVariant: ButtonVariant = 'primary';
  $: buttonVariant = $isLightTheme ? 'dark' : 'primary';
</script>

<div class="flex justify-center items-center flex-grow">
  <div class="w-full max-w-sm p-6 m-auto mx-auto bg-white rounded-lg shadow-md dark:bg-gray-800">
    <div class="flex justify-center mx-auto">
      <img class="w-auto h-7 sm:h-8" src="/favicon.png" alt="">
    </div>

    <form class="mt-6">
      <div class='mt-4'>
        <Input label="Username" name="username" />
      </div>

      <div class="mt-4">
        <Input label="Password" name="password" type="password">
          <a slot="postfix" href="/profile/forget" class="text-xs text-gray-600 dark:text-gray-400 hover:underline">Forget Password?</a>
        </Input>
      </div>

      <div class="mt-6">
        <Button variant={buttonVariant} size="md" class="w-full">
          Sign In
        </Button>
      </div>
    </form>

    <p class="mt-8 text-xs font-light text-center text-gray-400"> Don't have an account? <a href="/profile/signup" class="font-medium text-gray-700 dark:text-gray-200 hover:underline">Create One</a></p>
  </div>
</div>