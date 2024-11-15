<script lang="ts">
  import { className } from '@slink/utils/ui/className';

  import {
    HeadingContainer,
    HeadingDecoration,
    type HeadingProps,
    HeadingText,
  } from '@slink/components/UI/Text';

  interface $$Props extends HeadingProps {}

  export let variant: $$Props['variant'] = 'primary';
  export let size: $$Props['size'] = 'md';
  export let fontWeight: $$Props['fontWeight'] = 'semibold';
  export let alignment: $$Props['alignment'] = 'center';

  $: textClasses = HeadingText({
    size,
    fontWeight,
  });

  $: decorationClasses = HeadingDecoration({
    variant,
    size,
  });

  $: containerClasses = HeadingContainer({
    alignment,
  });
</script>

<div class={className(containerClasses)}>
  <h1 class={className(textClasses)}><slot>Heading</slot></h1>

  <div>
    <span class={className(`${decorationClasses} w-40 `)} />
    <span class={className(`${decorationClasses} mx-1 w-3`)} />
    <span class={className(`${decorationClasses} w-1`)} />
  </div>
</div>
