<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import {
    type Visibility,
    createVisibilityPreferenceState,
  } from './VisibilityPreferenceState.svelte';

  interface Props {
    visibility: Visibility;
    disabled?: boolean;
  }

  let { visibility, disabled = false }: Props = $props();

  const preference = createVisibilityPreferenceState(visibility);
</script>

<Button
  variant="glass"
  rounded="full"
  size="sm"
  class="min-w-[6.5rem]"
  disabled={disabled || preference.isLoading}
  onclick={() => preference.toggle()}
>
  {#if preference.isLoading}
    <Icon icon="svg-spinners:90-ring-with-bg" class="w-3.5 h-3.5" />
  {:else if preference.isPublic}
    <Icon icon="lucide:globe" class="w-3.5 h-3.5" />
  {:else}
    <Icon icon="lucide:lock" class="w-3.5 h-3.5" />
  {/if}

  {#if preference.isPublic}
    Public
  {:else}
    Private
  {/if}
</Button>
