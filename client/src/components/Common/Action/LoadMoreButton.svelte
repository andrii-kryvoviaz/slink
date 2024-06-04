<script lang="ts">
  import Icon from '@iconify/svelte';

  import { Button, type ButtonAttributes } from '@slink/components/Common';

  interface $$Props extends ButtonAttributes {
    visible?: boolean;
  }

  export let visible: $$Props['visible'] = true;

  $$props.class = `${$$props.class} w-40`;
</script>

{#if visible}
  <div class="flex justify-center">
    <Button size="md" variant="secondary" {...$$props} on:click>
      <slot name="text">Load More</slot>
      <slot name="icon" slot="rightIcon">
        <Icon icon="mynaui:chevron-double-down" />
      </slot>
    </Button>
  </div>
{/if}
