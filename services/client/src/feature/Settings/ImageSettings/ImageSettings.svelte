<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Notice } from '@slink/feature/Text';
  import { FileSizeInput, NumberInput } from '@slink/ui/components/input';
  import { Select } from '@slink/ui/components/select';
  import { Switch } from '@slink/ui/components/switch';

  import { t } from '$lib/i18n';

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
    { value: 'webp', label: 'settings.image.target_format.webp' },
    { value: 'avif', label: 'settings.image.target_format.avif' },
    { value: 'jpg', label: 'settings.image.target_format.jpg' },
  ];

  let targetFormat = $derived.by(() => settings.targetFormat ?? 'webp');
</script>

<SettingsPane category="image" {loading} on={{ save: onSave }}>
  {#snippet title()}
    {$t('settings.image.title')}
  {/snippet}
  {#snippet description()}
    {$t('settings.image.description')}
  {/snippet}

  <SettingItem
    defaultValue={defaultSettings?.maxSize}
    currentValue={settings.maxSize}
    reset={(value) => {
      settings.maxSize = value;
    }}
  >
    {#snippet label()}
      {$t('settings.image.max_size.label')}
    {/snippet}
    {#snippet hint()}
      {$t('settings.image.max_size.hint')}
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
      {$t('settings.image.strip_exif.label')}
    {/snippet}
    {#snippet hint()}
      {$t('settings.image.strip_exif.hint')}
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
      {$t('settings.image.allow_only_public.label')}
    {/snippet}
    {#snippet hint()}
      {$t('settings.image.allow_only_public.hint')}
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
      {$t('settings.image.dedup.label')}
    {/snippet}
    {#snippet hint()}
      {$t('settings.image.dedup.hint')}
    {/snippet}
    <Switch
      name="imageEnableDeduplication"
      bind:checked={settings.enableDeduplication}
    />

    {#snippet footer()}
      {#if settings.enableDeduplication}
        <Notice variant="info" appearance="subtle" size="sm" class="px-4">
          {$t('settings.image.dedup.notice')}
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
      {$t('settings.image.compression_quality.label')}
    {/snippet}
    {#snippet hint()}
      {$t('settings.image.compression_quality.hint')}
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
      {$t('settings.image.force_conversion.label')}
    {/snippet}
    {#snippet hint()}
      {$t('settings.image.force_conversion.hint')}
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
        {$t('settings.image.target_format.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.image.target_format.hint')}
      {/snippet}
      <Select
        items={targetFormatOptions.map((item) => ({
          ...item,
          label: $t(item.label),
        }))}
        value={targetFormat}
        onValueChange={(value: string) => {
          settings.targetFormat = value;
        }}
        placeholder={$t('settings.image.target_format.placeholder')}
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
        {$t('settings.image.convert_animated.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.image.convert_animated.hint')}
      {/snippet}
      <Switch
        name="imageConvertAnimatedImages"
        bind:checked={settings.convertAnimatedImages}
      />

      {#snippet footer()}
        <Notice variant="warning" appearance="subtle" size="sm" class="px-4">
          {$t('settings.image.convert_animated.notice_processing')}
        </Notice>
        {#if settings.convertAnimatedImages}
          {#if settings.targetFormat !== 'webp'}
            <Notice
              variant="warning"
              appearance="subtle"
              size="sm"
              class="px-4"
            >
              {$t('settings.image.convert_animated.notice_loss_prefix')}
              {settings.targetFormat?.toUpperCase()}
              {$t('settings.image.convert_animated.notice_loss_suffix')}
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
      {$t('settings.image.enable_licensing.label')}
    {/snippet}
    {#snippet hint()}
      {$t('settings.image.enable_licensing.hint')}
    {/snippet}
    <div class="flex justify-end">
      <Switch
        name="imageEnableLicensing"
        bind:checked={settings.enableLicensing}
      />
    </div>
  </SettingItem>
</SettingsPane>
