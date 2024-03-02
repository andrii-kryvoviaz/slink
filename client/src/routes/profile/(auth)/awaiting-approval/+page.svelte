<script lang="ts">
  import { fly } from 'svelte/transition';

  import { Button, CopyContainer } from '@slink/components/Common';

  import type { PageServerData } from './$types';

  export let data: PageServerData;
</script>

<section class="flex h-full items-center">
  <div
    class="container mx-auto flex flex-col items-center gap-4 px-4 py-12 text-center"
    in:fly={{ y: 100, duration: 500, delay: 100 }}
  >
    {#if data.status === 'inactive'}
      <h2
        class="mx-auto max-w-lg text-4xl font-light tracking-tight text-gray-800 dark:text-white xl:text-4xl"
      >
        Your account has been created and is awaiting approval.
        <span class="text-primary">Hooray 🚀</span>
      </h2>

      <div
        class="my-6 rounded-lg border-2 border-dashed px-8 py-5 text-2xl font-light uppercase tracking-wider dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
      >
        {data.status}
      </div>

      <p
        class="max-w-xl text-left font-extralight text-gray-500 dark:text-gray-300"
      >
        You can check your account status on this page at any time. While you
        waiting, you can send your email or user ID to the admin to speed up the
        process.
      </p>

      <div class=" flex w-full items-center justify-center gap-2">
        <span class="text-gray-500 dark:text-gray-300">Here is your ID:</span>
        <CopyContainer value={data.userId} />
      </div>
    {/if}
    {#if data.status === 'active'}
      <h2
        class="mx-auto max-w-lg text-4xl font-light tracking-tight text-gray-800 dark:text-white xl:text-4xl"
      >
        Your account has been approved 🎉
      </h2>

      <p
        class="mt-6 max-w-xl text-left font-extralight text-gray-500 dark:text-gray-300"
      >
        You can now login to your account and start using the platform.
      </p>

      <div class="mt-10 flex w-full items-center justify-center gap-2">
        <Button href="/profile/login" variant="primary" size="md"
          >Proceed to login</Button
        >
      </div>
    {/if}
  </div>
</section>
