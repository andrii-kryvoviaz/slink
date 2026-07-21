<script lang="ts">
  import { Toolbar as ToolbarPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  import { getToolbarContext } from './context';
  import {
    type ToolbarButtonShape,
    toolbarButtonVariants,
  } from './toolbar.theme';

  interface Props extends ToolbarPrimitive.ButtonProps {
    shape?: ToolbarButtonShape;
    active?: boolean;
    loading?: boolean;
  }

  let {
    shape,
    active = false,
    loading = false,
    class: className,
    children,
    child,
    ...restProps
  }: Props = $props();

  const context = getToolbarContext();
  const fallbackShape: ToolbarButtonShape = context ? 'segment' : 'pill';

  const buttonProps = $derived({
    class: cn(
      toolbarButtonVariants({
        shape: shape ?? fallbackShape,
        surface: context?.surface ?? 'floating',
        tone: context?.tone ?? 'dark',
        active,
        loading,
      }),
      className,
    ),
    'data-active': active ? '' : undefined,
    'data-loading': loading ? '' : undefined,
    ...restProps,
  });
</script>

{#if context?.withinRoot}
  <ToolbarPrimitive.Button {...buttonProps} {child} {children} />
{:else if child}
  {@render child({ props: buttonProps })}
{:else}
  <button {...buttonProps}>
    {@render children?.()}
  </button>
{/if}
