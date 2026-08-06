<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { Button } from '@slink/ui/components/button';
  import type { Snippet } from 'svelte';

  import type { SettingCategory } from '$lib/settings/Type/GlobalSettings';

  import { useSettingsPage } from '@slink/lib/state/SettingsPage.svelte';

  interface Props {
    category: SettingCategory;
    loading?: boolean;
    title?: Snippet;
    description?: Snippet;
    children?: Snippet<[Record<string, string>]>;
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

  const settingsPage = useSettingsPage();

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
          class="text-sm font-medium text-muted-foreground uppercase tracking-wider"
        >
          {@render title?.()}
        </h2>
      {/if}
      {#if description}
        <p class="text-xs text-foreground-subtle mt-1">
          {@render description?.()}
        </p>
      {/if}
    </div>
  </div>

  <form method="POST" onsubmit={handleSubmit}>
    <div
      class="divide-y divide-muted rounded-xl bg-muted-subtle/50 dark:bg-muted-subtle/30 border border-muted overflow-hidden"
    >
      {@render children?.(settingsPage.errors)}
    </div>

    <div class="flex items-center justify-end gap-3 pt-4">
      {#if loading}
        <div class="flex items-center gap-2 text-sm text-muted-foreground">
          <Loader variant="minimal" size="xs" />
          <span>Saving...</span>
        </div>
      {/if}

      {#if actions}
        {@render actions?.()}
      {/if}

      <Button
        type="submit"
        variant="soft-blue"
        rounded="full"
        size="sm"
        disabled={loading}
      >
        Save Changes
      </Button>
    </div>
  </form>
</section>
