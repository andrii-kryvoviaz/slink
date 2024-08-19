<script lang="ts">
  import Icon from '@iconify/svelte';

  import { Button, type ButtonAttributes } from '@slink/components/Common';

  interface $Props extends ButtonAttributes {
    loading: boolean;
  }

  export let loading: boolean;
</script>

<div>
  <Button
    variant="default"
    size="md"
    class="group"
    {loading}
    on:click
    {...$$restProps}
  >
    <Icon
      icon="teenyicons:refresh-solid"
      slot="rightIcon"
      class="transition-transform duration-500 group-hover:rotate-180"
    />
    Refresh
  </Button>
</div>
