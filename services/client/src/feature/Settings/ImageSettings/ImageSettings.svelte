<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Notice } from '@slink/feature/Text';
  import { FileSizeInput } from '@slink/ui/components/input';
  import { Switch } from '@slink/ui/components/switch';

  import type { SettingCategory } from '@slink/lib/settings/Type/GlobalSettings';
  import type { ImageSettings as ImageSettingsType } from '@slink/lib/settings/Type/ImageSettings';

  interface Props {
    settings: ImageSettingsType;
    defaultSettings?: ImageSettingsType;
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
</script>

<SettingsPane category="image" {loading} on={{ save: onSave }}>
  {#snippet title()}
    Image Settings
  {/snippet}
  {#snippet description()}
    Configure image upload limits and processing options
  {/snippet}

  <SettingItem
    defaultValue={defaultSettings?.maxSize}
    currentValue={settings.maxSize}
    reset={(value) => {
      settings.maxSize = value;
    }}
  >
    {#snippet label()}
      Maximum Image Size
    {/snippet}
    {#snippet hint()}
      Set the maximum size limit for image uploads
    {/snippet}
    <FileSizeInput name="imageMaxSize" bind:value={settings.maxSize} />
  </SettingItem>

  <SettingItem
    defaultValue={defaultSettings?.stripExifMetadata}
    currentValue={settings.stripExifMetadata}
    reset={(value) => {
      settings.stripExifMetadata = value;
    }}
  >
    {#snippet label()}
      Strip EXIF Metadata
    {/snippet}
    {#snippet hint()}
      Automatically remove metadata from uploaded images for privacy
    {/snippet}
    <Switch
      name="imageStripExifMetadata"
      bind:checked={settings.stripExifMetadata}
    />
  </SettingItem>

  <SettingItem
    defaultValue={defaultSettings?.allowOnlyPublicImages}
    currentValue={settings.allowOnlyPublicImages}
    reset={(value) => {
      settings.allowOnlyPublicImages = value;
    }}
  >
    {#snippet label()}
      Allow Only Public Images
    {/snippet}
    {#snippet hint()}
      When enabled, all images are automatically set to public and visibility
      cannot be changed
    {/snippet}
    <Switch
      name="imageAllowOnlyPublicImages"
      bind:checked={settings.allowOnlyPublicImages}
    />
  </SettingItem>

  <SettingItem
    defaultValue={defaultSettings?.enableDeduplication}
    currentValue={settings.enableDeduplication}
    reset={(value) => {
      settings.enableDeduplication = value;
    }}
  >
    {#snippet label()}
      Enable Image Deduplication
    {/snippet}
    {#snippet hint()}
      Detect and reject duplicate images during upload
    {/snippet}
    <Switch
      name="imageEnableDeduplication"
      bind:checked={settings.enableDeduplication}
    />
  </SettingItem>

  {#if settings.enableDeduplication}
    <Notice variant="info" appearance="subtle" size="sm" class="px-4">
      Only applies to new uploads. Existing images are not affected.
    </Notice>
  {/if}
</SettingsPane>
