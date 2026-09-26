<script lang="ts">
  import { Select } from '@slink/ui/components';
  import { untrack } from 'svelte';

  import type { OAuthProviderDetails } from '@slink/api/Resources/OAuthResource';

  import type { UserSettings as UserSettingsType } from '@slink/lib/settings/Type/UserSettings';

  interface Props {
    settings: UserSettingsType;
    providers: OAuthProviderDetails[];
    saving: boolean;
    failed: boolean;
    onSave: () => void;
  }

  let {
    settings = $bindable(),
    providers,
    saving,
    failed,
    onSave,
  }: Props = $props();

  const NONE = 'none';

  let saved = untrack(() => settings.autoRedirectProviderId);

  let enabledProviders = $derived(
    providers.filter((provider) => provider.enabled),
  );

  $effect(() => {
    if (saving) return;

    if (failed) {
      settings.autoRedirectProviderId = saved;
      return;
    }

    saved = untrack(() => settings.autoRedirectProviderId);
  });

  const pick = (value: string) => {
    const next = value === NONE ? null : value;
    if (next === settings.autoRedirectProviderId) return;

    settings.autoRedirectProviderId = next;
    onSave();
  };
</script>

{#if enabledProviders.length > 0}
  <div
    class="flex items-center justify-between gap-4 sm:gap-6 px-4 py-3.5 bg-muted-soft"
  >
    <div class="min-w-0 flex-1">
      <h3 class="text-sm font-medium text-foreground">Auto-redirect</h3>
      <p class="text-xs text-foreground-muted mt-0.5">
        Skip the login page and send visitors to this provider
      </p>
    </div>
    <div class="shrink-0">
      <Select
        items={[
          { value: NONE, label: 'None' },
          ...enabledProviders.map((provider) => ({
            value: provider.id,
            label: provider.name,
          })),
        ]}
        value={settings.autoRedirectProviderId ?? NONE}
        onValueChange={pick}
        placeholder="Select provider"
        busy={saving}
      />
    </div>
  </div>
{/if}
