<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Input } from '@slink/ui/components/input';

  import Icon from '@iconify/svelte';

  import type { CustomizationSettings as CustomizationSettingsType } from '@slink/lib/settings/Type/CustomizationSettings';
  import type { SettingCategory } from '@slink/lib/settings/Type/GlobalSettings';
  import { debounce } from '@slink/lib/utils/time/debounce';

  interface Props {
    settings: CustomizationSettingsType;
    defaultSettings?: CustomizationSettingsType;
    loading?: boolean;
    onSave: (event: {
      category: SettingCategory;
      data: Record<string, string | File>;
    }) => void;
  }

  let {
    settings = $bindable(),
    defaultSettings,
    loading = false,
    onSave,
  }: Props = $props();

  const defaultLogoUrl = '/favicon.png';

  let previewUrl = $state(defaultLogoUrl);
  let previewError = $state(false);

  const schedulePreview = debounce((url: string) => {
    previewUrl = url || defaultLogoUrl;
  }, 500);

  $effect(() => {
    const url = settings.logoUrl;
    previewError = false;
    schedulePreview(url);
  });
</script>

<SettingsPane category="customization" {loading} on={{ save: onSave }}>
  {#snippet title()}
    Customization
  {/snippet}
  {#snippet description()}
    Name and brand your Slink instance.
  {/snippet}

  {#snippet children(errors)}
    <SettingItem
      layout="stacked"
      defaultValue={defaultSettings?.siteName}
      currentValue={settings.siteName}
      reset={(value) => {
        settings.siteName = value;
      }}
    >
      {#snippet label()}
        Site Name
      {/snippet}
      {#snippet hint()}
        The public name of your instance.
      {/snippet}
      <Input
        name="customizationSiteName"
        bind:value={settings.siteName}
        placeholder="Slink"
        maxlength={64}
        variant="modern"
        size="md"
        rounded="lg"
        error={errors['customization.siteName']}
      />
    </SettingItem>

    <SettingItem
      layout="stacked"
      defaultValue={defaultSettings?.siteDescription}
      currentValue={settings.siteDescription}
      reset={(value) => {
        settings.siteDescription = value;
      }}
    >
      {#snippet label()}
        Site Description
      {/snippet}
      {#snippet hint()}
        A short tagline describing your instance.
      {/snippet}
      <Input
        name="customizationSiteDescription"
        bind:value={settings.siteDescription}
        placeholder="Fast and secure image sharing service"
        maxlength={255}
        variant="modern"
        size="md"
        rounded="lg"
        error={errors['customization.siteDescription']}
      />
    </SettingItem>

    <SettingItem
      layout="stacked"
      defaultValue={defaultSettings?.logoUrl}
      currentValue={settings.logoUrl}
      reset={(value) => {
        settings.logoUrl = value;
      }}
    >
      {#snippet label()}
        Logo URL
      {/snippet}
      {#snippet hint()}
        Link to your own logo image. Leave empty to use the default.
      {/snippet}
      <div class="flex items-center gap-3">
        <div class="flex-1">
          <Input
            name="customizationLogoUrl"
            type="url"
            inputmode="url"
            bind:value={settings.logoUrl}
            placeholder="https://example.com/logo.png"
            variant="modern"
            size="md"
            rounded="lg"
            error={errors['customization.logoUrl']}
          />
        </div>
        {#if previewError}
          <div
            class="flex h-9 shrink-0 items-center gap-1.5 rounded-lg border border-border bg-background px-3 text-xs text-muted-foreground"
          >
            <Icon icon="ph:image-broken" class="h-4 w-4" />
            <span>Couldn't load this image</span>
          </div>
        {:else}
          <img
            src={previewUrl}
            alt="Logo preview"
            class="h-9 w-9 shrink-0 rounded-lg border border-border bg-background object-contain p-1"
            onerror={() => {
              previewError = true;
            }}
          />
        {/if}
      </div>
    </SettingItem>
  {/snippet}
</SettingsPane>
