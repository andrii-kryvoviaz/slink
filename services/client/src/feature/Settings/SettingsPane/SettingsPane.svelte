<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { SettingsSection } from '@slink/feature/Settings';
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

<SettingsSection {title} {description}>
  {#snippet body(cards: Snippet)}
    <form method="POST" onsubmit={handleSubmit}>
      {@render cards()}

      <div class="flex items-center justify-end gap-3 pt-4">
        {#if loading}
          <div class="flex items-center gap-2 text-sm text-foreground-muted">
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
  {/snippet}

  {@render children?.(settingsPage.errors)}
</SettingsSection>
