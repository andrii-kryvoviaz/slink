<script lang="ts">
  import Icon from '@iconify/svelte';

  import { Tooltip } from '@slink/components/Common';
  import { Button } from '@slink/components/Common';
</script>

<header class="z-30 w-full border-b-2 border-header/70 backdrop-blur">
  <nav
    class="mx-auto flex min-h-[5rem] items-center justify-between px-5 py-4 sm:container lg:px-8"
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
          variant="link"
          size="sm"
          rounded="full"
          motion="hover:opacity"
          id="uploadImageLink"
          class="flex flex-row gap-2 hover:no-underline"
        >
          <Icon icon="simple-line-icons:plus" class="h-3 w-3 sm:h-5 sm:w-5" />
          <span class="inline sm:hidden">Upload</span>
        </Button>
        <Tooltip
          triggeredBy="[id^='uploadImageLink']"
          class="max-w-[10rem] p-2 text-center text-xs"
          color="dark"
          placement="bottom"
        >
          Upload an image
        </Tooltip>
      </div>

      <div class="hidden sm:block">
        <Button
          href="https://github.com/andrii-kryvoviaz/slink"
          variant="link"
          target="_blank"
          motion="hover:opacity"
          id="projectLink"
          class="p-2"
        >
          <Icon icon="akar-icons:github-fill" class="h-5 w-5" />
        </Button>
        <Tooltip
          triggeredBy="[id^='projectLink']"
          class="max-w-[10rem] p-2 text-center text-xs"
          color="dark"
          placement="bottom"
        >
          Project on GitHub
        </Tooltip>
      </div>

      {#if $$slots.themeSwitch}
        <p class="divider divider-horizontal m-0 hidden py-0 sm:flex" />
      {/if}

      <div class="hidden sm:flex">
        <slot name="themeSwitch" />
      </div>

      {#if $$slots.profile}
        <p class="divider divider-horizontal m-0 hidden py-0 sm:flex" />
      {/if}

      <slot name="profile" />
    </div>
  </nav>
</header>
