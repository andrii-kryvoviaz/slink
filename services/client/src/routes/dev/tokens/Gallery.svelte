<script lang="ts">
  import { AttributeChip } from '@slink/ui/components/attribute-chip';
  import chipSource from '@slink/ui/components/attribute-chip/attribute-chip.theme.ts?raw';
  import { Avatar, AvatarFallback } from '@slink/ui/components/avatar';
  import { Button } from '@slink/ui/components/button';
  import buttonSource from '@slink/ui/components/button/button.svelte?raw';
  import { CardRoot, CardTitle } from '@slink/ui/components/card';
  import cardSource from '@slink/ui/components/card/card.theme.ts?raw';
  import { Checkbox } from '@slink/ui/components/checkbox';
  import { Input } from '@slink/ui/components/input';
  import inputSource from '@slink/ui/components/input/enhanced-input.theme.ts?raw';
  import { Label } from '@slink/ui/components/label';
  import {
    ALERT_TYPES,
    MarkdownAlert,
  } from '@slink/ui/components/markdown-alert';
  import { SelectionPill } from '@slink/ui/components/pill';
  import pillSource from '@slink/ui/components/pill/selection-pill.svelte?raw';
  import { Progress } from '@slink/ui/components/progress';
  import progressSource from '@slink/ui/components/progress/Progress.svelte?raw';
  import { Separator } from '@slink/ui/components/separator';
  import { Skeleton } from '@slink/ui/components/skeleton';
  import { Switch } from '@slink/ui/components/switch';
  import type { ComponentProps } from 'svelte';

  import { variantValues } from './contract';
  import { labels } from './labels';

  type ButtonProps = ComponentProps<typeof Button>;
  type InputProps = ComponentProps<typeof Input>;
  type ProgressProps = ComponentProps<typeof Progress>;
  type PillProps = ComponentProps<typeof SelectionPill>;
  type ChipProps = ComponentProps<typeof AttributeChip>;
  type CardProps = ComponentProps<typeof CardRoot>;
  type CardTitleProps = ComponentProps<typeof CardTitle>;

  const buttonVariantValues = variantValues(
    buttonSource,
    'buttonVariants',
    'variant',
  ) as NonNullable<ButtonProps['variant']>[];
  const buttonSizes = variantValues(
    buttonSource,
    'buttonInnerVariants',
    'size',
  ) as NonNullable<ButtonProps['size']>[];
  const buttonRounded = variantValues(
    buttonSource,
    'buttonVariants',
    'rounded',
  ) as NonNullable<ButtonProps['rounded']>[];

  const inputVariantValues = variantValues(
    inputSource,
    'inputVariants',
    'variant',
  ) as NonNullable<InputProps['variant']>[];
  const inputSizes = variantValues(
    inputSource,
    'inputVariants',
    'size',
  ) as NonNullable<InputProps['size']>[];

  const progressVariantValues = variantValues(
    progressSource,
    'progressVariants',
    'variant',
  ) as NonNullable<ProgressProps['variant']>[];
  const progressSizes = variantValues(
    progressSource,
    'progressVariants',
    'size',
  ) as NonNullable<ProgressProps['size']>[];

  const pillVariantValues = variantValues(
    pillSource,
    'pillVariants',
    'variant',
  ) as NonNullable<PillProps['variant']>[];

  const chipStates = variantValues(
    chipSource,
    'attributeChip',
    'state',
  ) as NonNullable<ChipProps['state']>[];

  const cardElevations = variantValues(
    cardSource,
    'cardTheme',
    'elevation',
  ) as NonNullable<CardProps['elevation']>[];
  const cardTitleSizes = variantValues(
    cardSource,
    'cardTitleTheme',
    'size',
  ) as NonNullable<CardTitleProps['size']>[];
</script>

{#snippet group(name: string, content: import('svelte').Snippet)}
  <section class="border-border/60 rounded-lg border p-3">
    <code class="text-muted-foreground font-mono text-[11px]">{name}</code>
    <div class="mt-2 flex flex-wrap items-center gap-2">
      {@render content()}
    </div>
  </section>
{/snippet}

<div class="flex flex-col gap-4">
  {#snippet buttons()}
    {#each buttonVariantValues as variant (variant)}
      <Button {variant}>{variant}</Button>
    {/each}
    {#each buttonSizes as size (size)}
      <Button variant="primary" {size}>{size}</Button>
    {/each}
    {#each buttonRounded as rounded (rounded)}
      <Button variant="outline" {rounded}>{rounded}</Button>
    {/each}
    <Button variant="primary" loading={true}>{labels.sample}</Button>
    <Button variant="primary" disabled={true}>{labels.sample}</Button>
  {/snippet}
  {@render group(labels.button, buttons)}

  {#snippet inputs()}
    {#each inputVariantValues as variant (variant)}
      <span class="w-48">
        <Input {variant} label={variant} placeholder={labels.placeholder} />
      </span>
    {/each}
    {#each inputSizes as size (size)}
      <span class="w-48">
        <Input {size} label={size} placeholder={labels.placeholder} />
      </span>
    {/each}
    <span class="w-48">
      <Input label={labels.sample} error={labels.sample} />
    </span>
  {/snippet}
  {@render group(labels.input, inputs)}

  {#snippet controls()}
    <Label>{labels.sample}</Label>
    <Switch checked={true} />
    <Switch checked={false} />
    <Checkbox checked={true} />
    <Checkbox checked={false} />
    <Checkbox indeterminate={true} />
    <Checkbox disabled={true} />
  {/snippet}
  {@render group(labels.controls, controls)}

  {#snippet feedback()}
    <span class="w-32"><Separator /></span>
    <span class="h-6"><Separator orientation="vertical" /></span>
    <Skeleton class="h-6 w-24" />
    {#each progressVariantValues as variant (variant)}
      <span class="w-24"><Progress value={60} {variant} /></span>
    {/each}
    {#each progressSizes as size (size)}
      <span class="w-24"><Progress value={40} {size} showPercentage /></span>
    {/each}
  {/snippet}
  {@render group(labels.feedback, feedback)}

  {#snippet identity()}
    <Avatar>
      <AvatarFallback>{labels.initials}</AvatarFallback>
    </Avatar>
    {#each pillVariantValues as variant (variant)}
      <SelectionPill label={variant} {variant} />
    {/each}
    {#each chipStates as state (state)}
      <AttributeChip {state} label={state} />
    {/each}
    <AttributeChip state="set" label={labels.sample} onRemove={() => {}} />
  {/snippet}
  {@render group(labels.identity, identity)}

  {#snippet surfaces()}
    {#each cardElevations as elevation (elevation)}
      <CardRoot {elevation} class="w-56 p-3">
        {#each cardTitleSizes as size (size)}
          <CardTitle {size}>{elevation}</CardTitle>
        {/each}
      </CardRoot>
    {/each}
    {#each ALERT_TYPES as type (type)}
      <span class="w-72"><MarkdownAlert {type} content={type} /></span>
    {/each}
  {/snippet}
  {@render group(labels.surfaces, surfaces)}
</div>
