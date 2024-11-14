<script lang="ts">
  import type { SettingCategory } from '@slink/lib/settings/Type/GlobalSettings';
  import { createEventDispatcher } from 'svelte';

  import { Button, Loader } from '@slink/components/Common';

  export let category: SettingCategory;
  export let loading: boolean = false;

  const dispatch = createEventDispatcher<{
    save: {
      category: SettingCategory;
      data: Record<string, FormDataEntryValue>;
    };
  }>();

  const handleSubmit = (event: SubmitEvent) => {
    event.preventDefault();

    const formData = new FormData(event.target as HTMLFormElement);
    const data = Object.fromEntries(formData.entries());

    dispatch('save', {
      category,
      data,
    });
  };
</script>

<div class="flex flex-col gap-2">
  <div class="mb-6 border-b border-gray-200 pb-6 dark:border-gray-200/10">
    {#if $$slots.title}
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-light">
          <slot name="title" />
        </h2>
      </div>
    {/if}

    {#if $$slots.description}
      <div class="text-sm font-extralight text-gray-500">
        <slot name="description" />
      </div>
    {/if}
  </div>

  <form on:submit={handleSubmit} class="flex flex-col gap-4">
    <slot />

    <div class="mt-8 flex items-center justify-end gap-4">
      {#if loading}
        <Loader size="xs" />
      {/if}
      <Button type="submit" variant="primary" size="md" disabled={loading}>
        Save
      </Button>
    </div>
  </form>
</div>
