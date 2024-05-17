<script lang="ts">
  import Icon from '@iconify/svelte';

  import { Button, type ButtonProps } from '@slink/components/Common';

  interface $$Props extends ButtonProps {
    visible?: boolean;
  }

  export let visible: $$Props['visible'] = true;
  export let loading: $$Props['loading'] = false;

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
