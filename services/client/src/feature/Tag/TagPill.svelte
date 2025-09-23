<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { cn } from '@slink/utils/ui';

  interface Props {
    tag: Tag;
    removable?: boolean;
    onRemove?: () => void;
    variant?: 'default' | 'neon' | 'minimal';
    size?: 'sm' | 'md';
  }

  let {
    tag,
    removable = false,
    onRemove,
    variant = 'neon',
    size = 'sm',
  }: Props = $props();

  const pathSegments = $derived(() => {
    if (!tag.path) return [tag.name];
    // Handle path format like "#test/test1/test2"
    const cleanPath = tag.path.startsWith('#') ? tag.path.slice(1) : tag.path;
    return cleanPath.split('/').filter(Boolean);
  });
  const isNested = $derived(pathSegments().length > 1);
  const nestingLevel = $derived(pathSegments().length);

  const baseClasses =
    'inline-flex items-center gap-1.5 rounded-lg font-medium transition-all duration-200';

  const variantClasses = $derived({
    default:
      'bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
    neon: cn(
      'bg-gradient-to-r from-blue-500/10 to-purple-500/10 text-blue-600 dark:text-blue-400',
      'border border-blue-500/20 dark:border-blue-400/30',
      'shadow-sm hover:shadow-md hover:shadow-blue-500/20 dark:hover:shadow-blue-400/20',
      'hover:from-blue-500/15 hover:to-purple-500/15',
      'focus-within:ring-2 focus-within:ring-blue-500/30 focus-within:ring-offset-2',
      // Enhanced neon effect for nested tags
      nestingLevel > 1 &&
        'shadow-[0_0_0_1px_rgba(59,130,246,0.15)] dark:shadow-[0_0_0_1px_rgba(96,165,250,0.2)]',
    ),
    minimal:
      'bg-slate-50 text-slate-600 border border-slate-100 dark:bg-slate-900/50 dark:text-slate-400 dark:border-slate-800',
  });

  const sizeClasses = {
    sm: 'px-2.5 py-1 text-xs',
    md: 'px-3 py-1.5 text-sm',
  };
</script>

<div class={cn(baseClasses, variantClasses[variant], sizeClasses[size])}>
  <Icon
    icon={isNested
      ? 'ph:folder-open'
      : nestingLevel === 1
        ? 'ph:hash'
        : 'ph:tag'}
    class={cn(
      'h-3 w-3',
      variant === 'neon'
        ? 'text-blue-500 dark:text-blue-400'
        : 'text-slate-500',
    )}
  />

  {#if isNested}
    <div class="flex items-center gap-1">
      <span
        class={cn(
          'text-xs',
          variant === 'neon'
            ? 'text-blue-600/60 dark:text-blue-400/60'
            : 'text-slate-500',
        )}
      >
        #{pathSegments().slice(0, -1).join(' › ')}
      </span>
      <Icon
        icon="ph:caret-right"
        class={cn(
          'h-2.5 w-2.5',
          variant === 'neon'
            ? 'text-blue-500/60 dark:text-blue-400/60'
            : 'text-slate-400',
        )}
      />
      <span
        class={cn(
          'text-xs font-medium',
          variant === 'neon'
            ? 'text-blue-700 dark:text-blue-300'
            : 'text-slate-700 dark:text-slate-300',
        )}
      >
        {pathSegments()[pathSegments().length - 1]}
      </span>
    </div>
  {:else}
    <span
      class={cn(
        'text-xs font-medium',
        variant === 'neon'
          ? 'text-blue-700 dark:text-blue-300'
          : 'text-slate-700 dark:text-slate-300',
      )}
    >
      {tag.name}
    </span>
  {/if}

  {#if tag.imageCount > 0}
    <span
      class={cn(
        'px-1.5 py-0.5 rounded-full text-[10px] font-semibold',
        variant === 'neon'
          ? 'bg-blue-500/20 text-blue-600 dark:bg-blue-400/20 dark:text-blue-400'
          : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
      )}
    >
      {tag.imageCount}
    </span>
  {/if}

  {#if removable}
    <Button
      size="sm"
      variant="ghost"
      onclick={onRemove}
      aria-label="Remove {tag.name} tag"
      class={cn(
        'h-4 w-4 p-0 rounded-full ml-1 transition-colors',
        variant === 'neon'
          ? 'hover:bg-red-500/20 text-red-500 dark:text-red-400'
          : 'hover:bg-destructive/20 text-destructive',
      )}
    >
      <Icon icon="ph:x" class="h-2.5 w-2.5" />
    </Button>
  {/if}
</div>
