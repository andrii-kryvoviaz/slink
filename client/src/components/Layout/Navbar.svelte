<script lang="ts">
  import Icon from '@iconify/svelte';
  import { Tooltip } from 'flowbite-svelte';
  import { Button, ThemeSwitch } from '@slink/components/Common';

</script>

<header class="w-full backdrop-blur border-b-2 border-header/70">
  <nav class="mx-auto flex container items-center justify-between py-4 px-5 lg:px-8">
    <div class="flex lg:flex-1">
      <a href="/" class="-m-1.5 p-1.5 flex items-center gap-2">
        <span class="sr-only">Slink</span>
        <img class="h-8 w-auto" src="/favicon.png" alt="">
        <span>Slink</span>
      </a>
    </div>

    <div class="hidden sm:flex lg:flex-1 lg:justify-end lg:items-center gap-3">
      <div>
        <Button
          href="/upload"
          variant="link"
          size="sm"
          rounded="full"
          motion="hover:opacity"
          id="uploadImageLink"
        >
          <Icon icon="simple-line-icons:plus" class="h-5 w-5" />
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

      <div>
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

      <p class="divider divider-horizontal py-0 m-0"></p>

      <ThemeSwitch />

      <p class="divider divider-horizontal py-0 m-0"></p>

      <Button href="/profile/login"
              motion="hover:opacity"
              variant="link"
              class="hover:no-underline"
      >
        <span class="text-sm font-semibold leading-6">
          <span>Log in</span>
          <Icon icon="solar:login-broken" class="inline w-6 h-6" />
        </span>
      </Button>
    </div>
  </nav>
</header>
