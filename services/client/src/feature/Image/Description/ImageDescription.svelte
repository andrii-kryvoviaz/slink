<script lang="ts">
  import { EditableText } from '@slink/feature/Text';

  import Icon from '@iconify/svelte';

  interface Props {
    description: string;
    isLoading?: boolean;
    on?: {
      change: (description: string) => void;
    };
  }

  let { description, isLoading = false, on }: Props = $props();
</script>

<EditableText
  value={description}
  placeholder="Add a description..."
  emptyText="Click to add a description..."
  {isLoading}
  on={{ change: on?.change }}
>
  {#snippet header({ isLoading })}
    <div class="flex items-center gap-2 mb-1.5">
      <span class="text-sm font-medium text-gray-500 dark:text-gray-400"
        >Description</span
      >
      {#if isLoading}
        <Icon
          icon="lucide:loader-2"
          class="h-3.5 w-3.5 text-gray-400 animate-spin"
        />
      {/if}
    </div>
  {/snippet}
</EditableText>
