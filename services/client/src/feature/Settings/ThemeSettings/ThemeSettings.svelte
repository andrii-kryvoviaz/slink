<script lang="ts">
  import { ThemePicker, ThemePreview } from '@slink/feature/Layout';
  import { SettingItem } from '@slink/feature/Settings';
  import { Badge, Notice } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';
  import { Switch } from '@slink/ui/components/switch';
  import { tick } from 'svelte';

  import Icon from '@iconify/svelte';

  import { themeVariables } from '@slink/lib/settings/ThemeCatalog';
  import type {
    CustomTheme,
    CustomizationSettings,
  } from '@slink/lib/settings/Type/CustomizationSettings';
  import {
    describeThemes,
    themeLabel,
  } from '@slink/lib/settings/themes.svelte';

  import ThemeEditor from './ThemeEditor.svelte';
  import { ThemeManager } from './ThemeManager.svelte';

  interface Props {
    settings: CustomizationSettings;
    defaultSettings?: CustomizationSettings;
    errors: Record<string, string>;
  }

  let { settings = $bindable(), defaultSettings, errors }: Props = $props();

  const manager = new ThemeManager(() => settings);

  let editing = $state<CustomTheme | null>(null);
  let addButton: HTMLElement | null = $state(null);

  /** Focus moves to the row's first button, or to "Add Theme" if the row is gone. */
  const act = async (row: { id: string }, change: () => void) => {
    change();
    await tick();
    const target =
      document.querySelector<HTMLElement>(
        `[data-theme-id="${CSS.escape(row.id)}"] button`,
      ) ?? addButton;
    target?.focus();
  };

  const error = $derived(
    errors['customization.themes'] ??
      errors['customization.hiddenThemes'] ??
      errors['customization.defaultTheme'],
  );
</script>

<SettingItem
  defaultValue={defaultSettings?.themeSelectorEnabled}
  currentValue={settings.themeSelectorEnabled}
  reset={(value) => {
    settings.themeSelectorEnabled = value;
  }}
>
  {#snippet label()}
    Theme Selector
  {/snippet}
  {#snippet hint()}
    Let users pick their own theme. When off, everyone gets the default theme.
  {/snippet}
  <Switch
    name="customizationThemeSelectorEnabled"
    aria-label="Theme Selector"
    bind:checked={settings.themeSelectorEnabled}
  />
</SettingItem>

<SettingItem
  defaultValue={defaultSettings?.defaultTheme || 'default'}
  defaultLabel={themeLabel(
    manager.catalog,
    defaultSettings?.defaultTheme || 'default',
  )}
  currentValue={settings.defaultTheme || 'default'}
  reset={(value) => {
    settings.defaultTheme = value;
  }}
>
  {#snippet label()}
    Default Theme
  {/snippet}
  {#snippet hint()}
    Used for guests and for users who have not picked a theme.
  {/snippet}
  <ThemePicker
    themes={describeThemes(manager.catalog)}
    bind:value={
      () => manager.catalog.defaultTheme,
      (value) => {
        settings.defaultTheme = value;
      }
    }
    name="customizationDefaultTheme"
  />
</SettingItem>

<SettingItem layout="stacked">
  {#snippet label()}
    Themes
  {/snippet}
  {#snippet hint()}
    Add your own themes, or edit and hide the built-in ones.
  {/snippet}

  <ul class="space-y-2" data-slot="theme-list">
    {#each manager.rows as row (row.id)}
      <li
        class="flex flex-wrap items-center gap-x-4 gap-y-2 rounded-lg border border-border bg-background px-3 py-2"
        data-slot="theme-row"
        data-theme-id={row.id}
        data-hidden={row.hidden || undefined}
      >
        <div class="flex w-full min-w-0 items-center gap-2 sm:w-auto sm:flex-1">
          <span
            class="truncate text-sm font-medium text-foreground"
            class:opacity-50={row.hidden}
          >
            {row.name}
          </span>
          {#if row.hidden}
            <Badge variant="neutral" size="xs">Hidden</Badge>
          {:else if row.edited}
            <Badge variant="amber" size="xs">Edited</Badge>
          {:else if !row.builtIn}
            <Badge variant="blue" size="xs">Custom</Badge>
          {/if}
        </div>

        <div class="w-32 shrink-0" class:opacity-50={row.hidden}>
          {#if row.custom}
            <ThemePreview variables={themeVariables(row.custom)} />
          {:else}
            <ThemePreview theme={row.id} />
          {/if}
        </div>

        <div class="flex shrink-0 items-center gap-1">
          {#if row.hidden}
            <Button
              type="button"
              variant="ghost"
              size="xs"
              rounded="md"
              aria-label={`Restore ${row.name}`}
              onclick={() => act(row, () => manager.restore(row))}
            >
              <Icon icon="lucide:eye" class="h-4 w-4" />
            </Button>
          {:else}
            {#if row.edited}
              <Button
                type="button"
                variant="ghost"
                size="xs"
                rounded="md"
                aria-label={`Revert ${row.name}`}
                onclick={() => act(row, () => manager.revert(row))}
              >
                <Icon icon="lucide:rotate-ccw" class="h-4 w-4" />
              </Button>
            {/if}
            <Button
              type="button"
              variant="ghost"
              size="xs"
              rounded="md"
              aria-label={`Edit ${row.name}`}
              onclick={() => (editing = manager.edit(row))}
            >
              <Icon icon="lucide:pencil" class="h-4 w-4" />
            </Button>
            <Button
              type="button"
              variant="ghost"
              size="xs"
              rounded="md"
              aria-label={`Duplicate ${row.name}`}
              onclick={() => (editing = manager.duplicate(row))}
            >
              <Icon icon="lucide:copy" class="h-4 w-4" />
            </Button>
            <Button
              type="button"
              variant="ghost"
              size="xs"
              rounded="md"
              disabled={!manager.canRemove}
              aria-label={row.builtIn
                ? `Hide ${row.name}`
                : `Delete ${row.name}`}
              onclick={() => act(row, () => manager.remove(row))}
            >
              <Icon
                icon={row.builtIn ? 'lucide:eye-off' : 'lucide:trash-2'}
                class="h-4 w-4"
              />
            </Button>
          {/if}
        </div>
      </li>
    {/each}
  </ul>

  <div class="mt-3 flex items-center justify-between gap-4">
    <Button
      type="button"
      variant="outline"
      size="sm"
      rounded="lg"
      bind:ref={addButton}
      onclick={() => (editing = manager.draft())}
    >
      <Icon icon="lucide:plus" class="mr-1.5 h-4 w-4" />
      <span>Add Theme</span>
    </Button>
    {#if manager.isDirty}
      <span class="text-xs text-foreground-muted">
        Save to apply your theme changes.
      </span>
    {/if}
  </div>

  {#if error}
    <Notice variant="error" appearance="subtle" size="sm" class="mt-3">
      {error}
    </Notice>
  {/if}
</SettingItem>

{#if editing}
  <ThemeEditor
    theme={editing}
    onSave={(theme) => {
      manager.save(theme);
      editing = null;
    }}
    onClose={() => (editing = null)}
  />
{/if}
