<script lang="ts">
  import Button from '@slink/components/Shared/Action/Button.svelte';
  import Icon from '@iconify/svelte';
  import ThemeSwitch from '@slink/components/Shared/Action/ThemeSwitch.svelte';
  import { Tooltip } from 'flowbite-svelte';

</script>

<header class="none">
  <nav class="mx-auto flex container items-center justify-between p-6 lg:px-8">
    <div class="flex lg:flex-1">
      <a href='/' class="-m-1.5 p-1.5">
        <span class="sr-only">Slink</span>
        <img class="h-8 w-auto" src='/favicon.png' alt="">
      </a>
    </div>

    <div class="hidden sm:flex lg:flex-1 lg:justify-end lg:items-center gap-2">
      <Button
        href="/upload"
        variant="link"
        size="sm"
        rounded="full"
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
      <Button
        href='https://github.com/andrii-kryvoviaz/slink'
        variant='link'
        target='_blank'
        id="projectLink"
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
      <ThemeSwitch />
      <Button href='/profile/login' variant='link'>
        <span class="text-sm font-semibold leading-6">Log in <span aria-hidden="true">&rarr;</span></span>
      </Button>
    </div>
  </nav>
</header>
