<script lang="ts">
  import { randomId } from '@slink/utils/string/randomId';

  import { Badge } from '@slink/components/Content';

  export let defaultValue: string | null = null;

  const uniqueId = randomId('setting-item');
</script>

<div class="flex flex-wrap items-center justify-between gap-2 sm:flex-nowrap">
  {#if $$slots.label}
    <h2 class="text-md flex flex-col items-start gap-2 font-light">
      <span
        class="flex flex-col flex-wrap items-start gap-2 text-gray-800 dark:text-gray-400 md:flex-row"
      >
        <slot name="label" />

        {#if defaultValue}
          <span
            class="flex cursor-pointer items-center gap-2 rounded-full bg-gray-100 p-1 px-4 text-xs font-extralight text-gray-500 transition-colors duration-200 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
          >
            <span class="font-light">Default</span>
            <Badge variant="default" size="sm">{defaultValue}</Badge>
          </span>
        {/if}
      </span>

      {#if $$slots.hint}
        <span class="w-64 text-xs font-extralight text-gray-500">
          <slot name="hint" />
        </span>
      {/if}
    </h2>
  {/if}

  <slot>Isn't available at the moment</slot>
</div>
