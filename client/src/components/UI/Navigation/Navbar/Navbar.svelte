<script lang="ts">
  import Icon from '@iconify/svelte';

  import { Button } from '@slink/components/UI/Action';

  let { themeSwitch, profile } = $props();
</script>

<header class="z-30 w-full border-b-2 border-header/70">
  <nav
    class="mx-auto flex min-h-[5rem] items-center justify-between px-5 py-4 lg:px-8"
  >
    <div class="flex flex-wrap lg:flex-1">
      <a href="/" class="-m-1.5 flex items-center gap-2 p-1.5">
        <span class="sr-only">Slink</span>
        <img class="h-8 w-auto" src="/favicon.png" alt="" />
        <span>Slink</span>
      </a>
    </div>

    <div class="flex items-center justify-end gap-3 lg:flex-1">
      <div>
        <Button
          href="/upload"
          variant="dark"
          size="sm"
          rounded="full"
          motion="hover:opacity"
          id="uploadImageLink"
          class="flex flex-row gap-2 py-3 text-sm hover:no-underline sm:py-2 sm:text-xs"
        >
          <Icon icon="ph:plus-fill" class="h-3 w-3 sm:h-4 sm:w-4" />
          Upload
        </Button>
      </div>

      {#if themeSwitch}
        <p class="divider divider-horizontal m-0 hidden py-0 sm:flex"></p>
      {/if}

      <div class="hidden sm:flex">
        {@render themeSwitch?.()}
      </div>

      {#if profile}
        <p class="divider divider-horizontal m-0 hidden py-0 sm:flex"></p>
      {/if}

      {@render profile?.()}
    </div>
  </nav>
</header>
