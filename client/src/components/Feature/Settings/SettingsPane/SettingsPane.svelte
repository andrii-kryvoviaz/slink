<script lang="ts">
  import type { SettingCategory } from '@slink/lib/settings/Type/GlobalSettings';
  import type { Snippet } from 'svelte';

  import { Button } from '@slink/components/UI/Action';
  import { Loader } from '@slink/components/UI/Loader';

  interface Props {
    category: SettingCategory;
    loading?: boolean;
    title?: Snippet;
    description?: Snippet;
    children?: Snippet;
    on?: {
      save: (event: {
        category: SettingCategory;
        data: Record<string, FormDataEntryValue>;
      }) => void;
    };
  }

  let {
    category,
    loading = false,
    title,
    description,
    children,
    on,
  }: Props = $props();

  const handleSubmit = (event: SubmitEvent) => {
    event.preventDefault();

    const formData = new FormData(event.target as HTMLFormElement);
    const data = Object.fromEntries(formData.entries());

    on?.save({ category, data });
  };
</script>

<div class="flex flex-col gap-2">
  <div class="mb-6 border-b border-gray-200 pb-6 dark:border-gray-200/10">
    {#if title}
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-light">
          {@render title?.()}
        </h2>
      </div>
    {/if}

    {#if description}
      <div class="text-sm font-extralight text-gray-500">
        {@render description?.()}
      </div>
    {/if}
  </div>

  <form onsubmit={handleSubmit} class="flex flex-col gap-4">
    {@render children?.()}

    <div class="mt-8 flex items-center justify-end gap-4">
      {#if loading}
        <Loader size="xs" />
      {/if}
      <Button
        type="submit"
        variant="primary"
        size="sm"
        class="py-3"
        disabled={loading}
      >
        Save Settings
      </Button>
    </div>
  </form>
</div>
