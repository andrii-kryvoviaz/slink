<script lang="ts">
  import Icon from '@iconify/svelte';

  import { randomId } from '@slink/utils/string/randomId';

  import {
    Button,
    type ButtonAttributes,
    Tooltip,
  } from '@slink/components/Common';

  interface $$Props extends ButtonAttributes {
    loading?: boolean;
  }

  export let loading: boolean = false;

  const uniqueId = randomId('refreshButton');
</script>

<div>
  <Button
    variant="default"
    size="md"
    class="group"
    id={uniqueId}
    {loading}
    on:click
    {...$$restProps}
  >
    <Icon
      icon="teenyicons:refresh-solid"
      slot="rightIcon"
      class="transition-transform duration-500 group-hover:rotate-180"
    />

    <Icon
      icon="teenyicons:refresh-solid"
      slot="loadingIcon"
      class="animate-spin"
    />

    <slot />

    {#if !loading}
      <Tooltip
        triggeredBy={`[id^='${uniqueId}']`}
        class="py-1 text-[0.7rem]"
        placement="left"
      >
        Refresh
      </Tooltip>
    {/if}
  </Button>
</div>
