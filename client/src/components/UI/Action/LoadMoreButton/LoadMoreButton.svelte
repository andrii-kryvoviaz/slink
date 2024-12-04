<script lang="ts">
  import Icon from '@iconify/svelte';

  import { Button, type ButtonAttributes } from '@slink/components/UI/Action';

  interface $$Props extends ButtonAttributes {
    visible?: boolean;
  }

  export let visible: $$Props['visible'] = true;

  $$props.class = `${$$props.class}`;
</script>

{#if visible}
  <div class="flex justify-center">
    <Button size="md" variant="secondary" {...$$props} on:click>
      <slot name="text">Load More</slot>
      <slot name="icon" slot="leftIcon">
        <Icon icon="teenyicons:refresh-solid" class="h-3 w-3" />
      </slot>
      <slot name="loadingIcon" slot="loadingIcon">
        <Icon icon="teenyicons:refresh-solid" class="h-3 w-3 animate-spin" />
      </slot>
    </Button>
  </div>
{/if}
