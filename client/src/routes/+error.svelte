<script>
  import { page } from '$app/stores';
  import { Button } from '@slink/components/Common';
</script>

<div class="flex h-full w-full flex-col items-center justify-center">
  <div class="flex flex-col items-center justify-center p-12">
    <h1 class="text-[8rem] font-light">
      {$page.status}
    </h1>
    <h2 class="text-2xl font-bold">
      {$page.error?.message}
    </h2>
  </div>
  <Button variant="primary" size="lg" rounded="full" target="_self" href="/"
    >Go Home</Button>
</div>
