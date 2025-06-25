<script lang="ts">
  import { enhance } from '$app/forms';

  interface Props {
    icon: string;
    title: string;
    href?: string;
    isForm?: boolean;
    variant?: 'default' | 'destructive';
    onClick?: () => void;
  }

  let {
    icon,
    title,
    href,
    isForm = false,
    variant = 'default',
    onClick,
  }: Props = $props();

  const baseClasses =
    'flex items-center gap-3 w-full px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-900/20 dark:focus-visible:ring-neutral-100/20';

  const variantClasses = {
    default:
      'text-neutral-700 hover:bg-neutral-100/70 dark:text-neutral-300 dark:hover:bg-neutral-800/70',
    destructive:
      'text-red-600 hover:bg-red-50/70 dark:text-red-400 dark:hover:bg-red-950/30',
  };

  const classes = `${baseClasses} ${variantClasses[variant]}`;
</script>

{#if isForm && href}
  <form action={href} method="POST" use:enhance>
    <button type="submit" class={classes} onclick={onClick}>
      <iconify-icon {icon} class="h-4 w-4"></iconify-icon>
      <span class="flex-1">{title}</span>
    </button>
  </form>
{:else if href}
  <a {href} class={classes} onclick={onClick}>
    <iconify-icon {icon} class="h-4 w-4"></iconify-icon>
    <span class="flex-1">{title}</span>
  </a>
{:else}
  <button type="button" class={classes} onclick={onClick}>
    <iconify-icon {icon} class="h-4 w-4"></iconify-icon>
    <span class="flex-1">{title}</span>
  </button>
{/if}
