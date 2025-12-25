<script lang="ts">
  import { TagSelector } from '@slink/feature/Tag';
  import { Button } from '@slink/ui/components/button';
  import { Input } from '@slink/ui/components/input';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  interface Props {
    tags: Tag[];
    isCreating: boolean;
    onSubmit: (data: { name: string; parentId?: string }) => void;
    onCancel: () => void;
    errors?: Record<string, string>;
  }

  let { tags, isCreating, onSubmit, onCancel, errors = {} }: Props = $props();

  let formData = $state({
    name: '',
    parentId: '',
  });

  const selectedParentTag = $derived(() => {
    if (!formData.parentId) return [];
    const tag = tags.find((t) => t.id === formData.parentId);
    return tag ? [tag] : [];
  });

  const handleParentTagChange = (selectedTags: Tag[]) => {
    formData.parentId = selectedTags.length > 0 ? selectedTags[0].id : '';
  };

  function handleSubmit(event: Event) {
    event.preventDefault();
    const data = {
      name: formData.name.trim(),
      parentId: formData.parentId || undefined,
    };
    onSubmit(data);
  }

  function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter' && !isCreating) {
      event.preventDefault();
      handleSubmit(event);
    }
  }
</script>

<div class="space-y-6">
  <div class="flex items-center gap-4">
    <div
      class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500/10 to-teal-600/15 dark:from-emerald-400/20 dark:to-teal-500/25 border border-emerald-300/40 dark:border-emerald-600/50 flex items-center justify-center shadow-md backdrop-blur-sm"
    >
      <Icon
        icon="lucide:tag"
        class="h-6 w-6 text-emerald-700 dark:text-emerald-300 drop-shadow-sm"
      />
    </div>
    <div>
      <h3
        class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight"
      >
        Create Tag
      </h3>
      <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
        Add a new tag to organize your images
      </p>
    </div>
  </div>

  <form onsubmit={handleSubmit} class="space-y-6">
    <div class="space-y-5">
      <Input
        label="Tag Name"
        bind:value={formData.name}
        placeholder="e.g., Nature, Photography, Art"
        error={errors?.name}
        size="md"
        rounded="lg"
        required
        onkeydown={handleKeydown}
      >
        {#snippet leftIcon()}
          <Icon icon="lucide:hash" class="h-4 w-4 text-slate-400" />
        {/snippet}
      </Input>

      <div class="space-y-3">
        <label
          for="parent-tag"
          class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3 block"
        >
          Parent Tag (Optional)
        </label>
        <TagSelector
          selectedTags={selectedParentTag()}
          onTagsChange={handleParentTagChange}
          placeholder="Search for parent tag..."
          variant="minimal"
          allowCreate={false}
        />
      </div>
    </div>

    <div
      class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-50/90 via-white to-teal-50/80 dark:from-emerald-950/20 dark:via-slate-800/50 dark:to-teal-950/30 border border-emerald-200/40 dark:border-emerald-800/30 p-5 shadow-lg backdrop-blur-sm"
    >
      <div
        class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-emerald-100/30 via-transparent to-teal-100/20 dark:from-emerald-900/20 dark:via-transparent dark:to-teal-900/10"
      ></div>
      <div class="relative flex items-start gap-4">
        <div class="flex-shrink-0 mt-0.5">
          <div
            class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg"
          >
            <Icon
              icon="lucide:info"
              class="h-5 w-5 text-white drop-shadow-sm"
            />
          </div>
        </div>
        <div class="flex-1 min-w-0">
          <h4
            class="text-sm font-semibold text-emerald-900 dark:text-emerald-100 leading-tight mb-2"
          >
            Tag Organization
          </h4>
          <p
            class="text-sm text-emerald-800/90 dark:text-emerald-200/90 leading-relaxed"
          >
            Tags help organize your images. Parent tags create a hierarchy for
            better organization. You can always move tags later or create
            sub-tags within existing ones.
          </p>
        </div>
      </div>
    </div>

    <div class="flex gap-3 pt-2">
      <Button
        variant="glass"
        size="sm"
        rounded="full"
        type="button"
        onclick={onCancel}
        disabled={isCreating}
        class="flex-1"
      >
        Cancel
      </Button>
      <Button
        variant="gradient-green"
        size="sm"
        rounded="full"
        type="submit"
        class="flex-1 shadow-lg hover:shadow-xl transition-shadow duration-200"
        disabled={isCreating || !formData.name.trim()}
      >
        {#if isCreating}
          <Icon icon="lucide:loader-2" class="h-4 w-4 mr-2 animate-spin" />
          Creating...
        {:else}
          <Icon icon="lucide:plus" class="h-4 w-4 mr-2" />
          Create Tag
        {/if}
      </Button>
    </div>
  </form>
</div>
