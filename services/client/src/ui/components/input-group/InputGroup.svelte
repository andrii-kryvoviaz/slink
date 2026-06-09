<script lang="ts">
  import type { VariantProps } from 'class-variance-authority';
  import type { Snippet } from 'svelte';

  import type { HTMLAttributes } from 'svelte/elements';

  import { cn } from '@slink/utils/ui/index.js';

  import { InputGroupShell } from './input-group.theme';

  type ShellVariants = VariantProps<typeof InputGroupShell>;

  interface Props extends HTMLAttributes<HTMLDivElement> {
    variant?: ShellVariants['variant'];
    size?: ShellVariants['size'];
    fluid?: ShellVariants['fluid'];
    class?: string;
    children: Snippet<[]>;
  }

  let {
    variant,
    size,
    fluid,
    class: className,
    children,
    ...rest
  }: Props = $props();
</script>

<div class={cn(InputGroupShell({ variant, size, fluid }), className)} {...rest}>
  {@render children()}
</div>
