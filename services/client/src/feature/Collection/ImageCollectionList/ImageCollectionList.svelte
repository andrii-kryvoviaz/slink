<script lang="ts">
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';

  import Icon from '@iconify/svelte';

  import { routes } from '@slink/lib/utils/url/routes';

  interface CollectionSummary {
    id: string;
    name: string;
  }

  interface Props {
    collections?: CollectionSummary[];
    class?: string;
  }

  let { collections = [], class: className = '' }: Props = $props();
</script>

{#if collections.length > 0}
  <div class="flex flex-wrap gap-2 {className}">
    {#each collections as collection (collection.id)}
      <a
        href={routes.collection.detail(collection.id)}
        class="inline-flex"
        title={collection.name}
      >
        <Badge variant="neon" size="sm" class="shrink-0">
          <div class="flex items-center gap-1.5">
            <Icon icon="ph:folder" class="h-3 w-3" />
            <span class="font-medium truncate max-w-[10rem]">
              {collection.name}
            </span>
          </div>
        </Badge>
      </a>
    {/each}
  </div>
{/if}
