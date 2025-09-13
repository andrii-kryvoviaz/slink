<script lang="ts">
  import { DropdownMenu } from 'bits-ui';

  type Props = DropdownMenu.GroupProps & {
    heading?: string;
  };

  let { heading, children }: Props = $props();
</script>

<DropdownMenu.Group>
  {#if heading}
    <DropdownMenu.GroupHeading>{heading}</DropdownMenu.GroupHeading>
  {/if}

  {@render children?.()}
</DropdownMenu.Group>
