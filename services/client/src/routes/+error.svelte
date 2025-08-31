<script lang="ts">
  import { Button } from '@slink/legacy/UI/Action';

  import { page } from '$app/state';
</script>

<div class="flex h-full w-full flex-col items-center justify-center">
  <div class="flex flex-col items-center justify-center p-12">
    <h1 class="text-[8rem] font-extralight">
      {page.status}
    </h1>
    <h2 class="text-2xl font-light">
      {page.error?.message}
    </h2>
  </div>

  <Button
    variant="primary"
    size="md"
    rounded="lg"
    target="_self"
    href={page.error?.button?.href || '/'}
    >{page.error?.button?.text || 'Take me Home'}</Button
  >
</div>
