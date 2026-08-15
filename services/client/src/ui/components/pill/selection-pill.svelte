<script lang="ts" module>
  import { type VariantProps, cva } from 'class-variance-authority';

  const pillVariants = cva(
    'inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-full transition-colors disabled:opacity-50 disabled:cursor-not-allowed group',
    {
      variants: {
        variant: {
          indigo:
            'bg-accent-wash dark:bg-accent-wash/20 text-accent-text hover:bg-accent-solid/30',
          blue: 'bg-info-wash dark:bg-info-wash/20 text-info-text hover:bg-primary-solid/30',
          emerald:
            'bg-success-wash dark:bg-success-wash/20 text-success-text hover:bg-success/30',
          amber:
            'bg-warning-wash dark:bg-warning-wash/20 text-warning-text hover:bg-warning/30',
          rose: 'bg-danger-wash dark:bg-danger-wash/20 text-danger-text hover:bg-danger/30',
          slate: 'bg-muted text-foreground-soft hover:bg-border',
        },
      },
      defaultVariants: {
        variant: 'indigo',
      },
    },
  );

  export type PillVariant = VariantProps<typeof pillVariants>['variant'];
</script>

<script lang="ts">
  import Icon from '@iconify/svelte';

  interface Props {
    label: string;
    icon?: string;
    variant?: PillVariant;
    disabled?: boolean;
    onRemove?: () => void;
  }

  let {
    label,
    icon,
    variant = 'indigo',
    disabled = false,
    onRemove,
  }: Props = $props();
</script>

<span
  class={pillVariants({ variant })}
  class:opacity-50={disabled}
  class:cursor-not-allowed={disabled}
>
  {#if icon}
    <Icon {icon} class="w-3.5 h-3.5" />
  {/if}
  <span class="font-medium">{label}</span>
  <button
    type="button"
    onclick={onRemove}
    {disabled}
    class="p-0.5 -mr-1 rounded-full hover:bg-foreground/10 transition-colors disabled:pointer-events-none"
  >
    <Icon
      icon="ph:x"
      class="w-3 h-3 opacity-50 group-hover:opacity-100 transition-opacity"
    />
  </button>
</span>
