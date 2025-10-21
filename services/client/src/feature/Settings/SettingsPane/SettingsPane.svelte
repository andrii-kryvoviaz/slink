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
    description,
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

<div
  class="bg-white dark:bg-gray-900/50 border border-gray-200/50 dark:border-gray-700/30 rounded-2xl p-8 backdrop-blur-sm shadow-sm hover:shadow-md transition-all duration-200"
>
  <div class="mb-8 pb-6 border-b border-gray-200/60 dark:border-gray-700/40">
    {#if title}
      <h2
        class="text-2xl font-light text-gray-900 dark:text-white mb-2 tracking-tight"
      >
        {@render title?.()}
      </h2>
    {/if}

    {#if description}
      <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
        {@render description?.()}
      </p>
    {/if}
  </div>

  <form onsubmit={handleSubmit} class="space-y-8">
    <div class="space-y-6">
      {@render children?.()}
    </div>

    <div
      class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200/60 dark:border-gray-700/40"
    >
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

      <Button type="submit" variant="outline" size="sm" disabled={loading}>
        Save Changes
      </Button>
    </div>
  </form>
</div>
