<script lang="ts">
  import { Button, type ButtonVariant } from '@slink/components/Common';
  import { isLightTheme } from '@slink/store/settings.js';

  let buttonVariant: ButtonVariant = 'primary';
  $: buttonVariant = $isLightTheme ? 'dark' : 'primary';
</script>

<div class="flex justify-center items-center flex-grow">
  <div class="w-full max-w-sm p-6 m-auto mx-auto bg-white rounded-lg shadow-md dark:bg-gray-800">
    <div class="flex justify-center mx-auto">
      <img class="w-auto h-7 sm:h-8" src="/favicon.png" alt="">
    </div>

    <form class="mt-6">
      <div>
        <label for="username" class="block text-sm text-gray-800 dark:text-gray-200">Username</label>
        <input type="text" class="block w-full px-4 py-2 mt-2 text-gray-700 bg-white border rounded-lg dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 focus:border-blue-400 dark:focus:border-blue-300 focus:ring-blue-300 focus:outline-none focus:ring focus:ring-opacity-40" />
      </div>

      <div class="mt-4">
        <div class="flex items-center justify-between">
          <label for="password" class="block text-sm text-gray-800 dark:text-gray-200">Password</label>
          <a href="#" class="text-xs text-gray-600 dark:text-gray-400 hover:underline">Forget Password?</a>
        </div>

        <input type="password" class="block w-full px-4 py-2 mt-2 text-gray-700 bg-white border rounded-lg dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 focus:border-blue-400 dark:focus:border-blue-300 focus:ring-blue-300 focus:outline-none focus:ring focus:ring-opacity-40" />
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