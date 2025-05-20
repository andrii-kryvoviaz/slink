<script lang="ts">
  import { DropdownMenu } from 'bits-ui';

  type Props = DropdownMenu.GroupProps & {
    heading?: string;
  };

  let { heading, children, ...props }: Props = $props();
</script>

<DropdownMenu.Group>
  {#if heading}
    <DropdownMenu.GroupHeading>{heading}</DropdownMenu.GroupHeading>
  {/if}

  {@render children?.()}
</DropdownMenu.Group>
