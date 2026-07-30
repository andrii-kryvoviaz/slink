<script lang="ts">
  import CircleIcon from '@lucide/svelte/icons/circle';
  import { RadioGroup as RadioGroupPrimitive } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import { type WithoutChildrenOrChild, cn } from '@slink/utils/ui/index.js';

  import {
    RadioGroupCardBodyTheme,
    RadioGroupCardDescriptionTheme,
    RadioGroupCardDotTheme,
    RadioGroupCardIndicatorTheme,
    RadioGroupCardTheme,
    RadioGroupCardTitleTheme,
    type RadioGroupCardTone,
  } from './radio-group.theme';

  type Props = Omit<
    WithoutChildrenOrChild<RadioGroupPrimitive.ItemProps>,
    'title'
  > & {
    tone?: RadioGroupCardTone;
    title: Snippet;
    description?: Snippet;
  };

  let {
    ref = $bindable(null),
    class: className,
    tone = 'default',
    title,
    description,
    ...restProps
  }: Props = $props();
</script>

<RadioGroupPrimitive.Item
  bind:ref
  data-slot="radio-group-card"
  class={cn(RadioGroupCardTheme({ tone }), className)}
  {...restProps}
>
  {#snippet children({ checked })}
    <span class={RadioGroupCardIndicatorTheme({ tone })}>
      {#if checked}
        <CircleIcon class={RadioGroupCardDotTheme({ tone })} />
      {/if}
    </span>
    <span class={RadioGroupCardBodyTheme()}>
      <span class={RadioGroupCardTitleTheme({ tone })}>{@render title()}</span>
      {#if description}
        <span class={RadioGroupCardDescriptionTheme()}
          >{@render description()}</span
        >
      {/if}
    </span>
  {/snippet}
</RadioGroupPrimitive.Item>
