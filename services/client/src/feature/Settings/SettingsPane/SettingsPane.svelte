<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { Button } from '@slink/ui/components/button';
  import type { Snippet } from 'svelte';

  import type { SettingCategory } from '$lib/settings/Type/GlobalSettings';

  interface Props {
    category: SettingCategory;
    loading?: boolean;
    title?: Snippet;
    description?: Snippet;
    children?: Snippet;
    actions?: Snippet;
    on?: {
      save: (event: {
        category: SettingCategory;
        data: Record<string, string | File>;
      }) => void;
    };
  }

  let {
    category,
    loading = false,
    title,
    children,
    actions,
    on,
  }: Props = $props();

  const handleSubmit = (event: SubmitEvent) => {
    event.preventDefault();

    const formData = new FormData(event.target as HTMLFormElement);
    const formDataEntries = Object.fromEntries(formData.entries());

    on?.save({ category, data: formDataEntries });
  };
</script>

<section class="space-y-1">
  <div class="flex items-center justify-between gap-4 pb-3">
    <div>
      {#if title}
        <h2
          class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
        >
          {@render title?.()}
        </h2>
      {/if}
    </div>
  </div>

  <form onsubmit={handleSubmit}>
    <div
      class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 overflow-hidden"
    >
      {@render children?.()}
    </div>

    <div class="flex items-center justify-end gap-3 pt-4">
      {#if loading}
        <div
          class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
        >
          <Loader variant="minimal" size="xs" />
          <span>Saving...</span>
        </div>
      {/if}

      {#if actions}
        {@render actions?.()}
      {/if}

      <Button
        type="submit"
        variant="glass-blue"
        rounded="full"
        size="sm"
        disabled={loading}
      >
        Save Changes
      </Button>
    </div>
  </form>
</section>
