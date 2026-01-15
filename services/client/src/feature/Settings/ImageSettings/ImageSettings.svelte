<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Notice } from '@slink/feature/Text';
  import { FileSizeInput, NumberInput } from '@slink/ui/components/input';
  import { Select } from '@slink/ui/components/select';
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

  const targetFormatOptions = [
    { value: 'webp', label: 'WEBP (Recommended)' },
    { value: 'avif', label: 'AVIF' },
    { value: 'jpg', label: 'JPEG' },
  ];

  let targetFormat = $derived.by(() => settings.targetFormat ?? 'webp');
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

    {#snippet footer()}
      {#if settings.enableDeduplication}
        <Notice variant="info" appearance="subtle" size="sm" class="px-4">
          Only applies to new uploads. Existing images are not affected.
        </Notice>
      {/if}
    {/snippet}
  </SettingItem>

  <SettingItem
    defaultValue={defaultSettings?.compressionQuality}
    currentValue={settings.compressionQuality}
    reset={(value) => {
      settings.compressionQuality = value;
    }}
  >
    {#snippet label()}
      Compression Quality
    {/snippet}
    {#snippet hint()}
      Quality level for lossy image compression (1-100). Used during format
      conversion if target format supports it
    {/snippet}
    <NumberInput
      name="imageCompressionQuality"
      bind:value={settings.compressionQuality}
      min={1}
      max={100}
      step={1}
    />
  </SettingItem>

  <SettingItem
    defaultValue={defaultSettings?.forceFormatConversion}
    currentValue={settings.forceFormatConversion}
    reset={(value) => {
      settings.forceFormatConversion = value;
    }}
  >
    {#snippet label()}
      Force Format Conversion
    {/snippet}
    {#snippet hint()}
      Automatically convert all uploaded images to a specific format
    {/snippet}
    <Switch
      name="imageForceFormatConversion"
      bind:checked={settings.forceFormatConversion}
    />
  </SettingItem>

  {#if settings.forceFormatConversion}
    <SettingItem
      defaultValue={defaultSettings?.targetFormat}
      currentValue={settings.targetFormat}
      reset={(value) => {
        settings.targetFormat = value;
      }}
    >
      {#snippet label()}
        Target Format
      {/snippet}
      {#snippet hint()}
        Format to convert uploaded images to
      {/snippet}
      <Select
        items={targetFormatOptions}
        value={targetFormat}
        onValueChange={(value: string) => {
          settings.targetFormat = value;
        }}
        placeholder="Select format"
      />
    </SettingItem>

    <SettingItem
      defaultValue={defaultSettings?.convertAnimatedImages}
      currentValue={settings.convertAnimatedImages}
      reset={(value) => {
        settings.convertAnimatedImages = value;
      }}
    >
      {#snippet label()}
        Convert Animated Images
      {/snippet}
      {#snippet hint()}
        Include animated images (GIF, animated WebP/AVIF) in conversion
      {/snippet}
      <Switch
        name="imageConvertAnimatedImages"
        bind:checked={settings.convertAnimatedImages}
      />

      {#snippet footer()}
        <Notice variant="warning" appearance="subtle" size="sm" class="px-4">
          Processing animated images is CPU-intensive and may slow down uploads.
        </Notice>
        {#if settings.convertAnimatedImages}
          {#if settings.targetFormat !== 'webp'}
            <Notice
              variant="warning"
              appearance="subtle"
              size="sm"
              class="px-4"
            >
              Converting to {settings.targetFormat?.toUpperCase()} will result in
              loss of animation.
            </Notice>
          {/if}
        {/if}
      {/snippet}
    </SettingItem>
  {/if}

  <SettingItem
    defaultValue={defaultSettings?.enableLicensing}
    currentValue={settings.enableLicensing}
    reset={(value) => {
      settings.enableLicensing = value;
    }}
  >
    {#snippet label()}
      Enable Licensing
    {/snippet}
    {#snippet hint()}
      Allow users to set licenses on their images (Creative Commons, etc.)
    {/snippet}
    <div class="flex justify-end">
      <Switch
        name="imageEnableLicensing"
        bind:checked={settings.enableLicensing}
      />
    </div>
  </SettingItem>
</SettingsPane>
