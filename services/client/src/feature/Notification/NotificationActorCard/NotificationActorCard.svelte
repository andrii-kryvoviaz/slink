<script lang="ts">
  import { UserAvatar } from '@slink/feature/User';
  import {
    Popover,
    PopoverContent,
    PopoverTrigger,
  } from '@slink/ui/components/popover';
  import { ScrollArea } from '@slink/ui/components/scroll-area';

  import { plural } from '$lib/utils/i18n';
  import Icon from '@iconify/svelte';

  import type { NotificationGroup } from '@slink/utils/notification';

  import { notificationActorCard } from './NotificationActorCard.theme';

  interface Props {
    group: NotificationGroup;
  }

  let { group }: Props = $props();

  const theme = notificationActorCard();
</script>

{#snippet header()}
  <div class={theme.header()}>
    <Icon icon="ph:bookmark-simple-fill" class={theme.headerIcon()} />
    <span class={theme.headerLabel()}>Bookmarked</span>
    <span class={theme.headerCount()}>{group.actorTotal}</span>
  </div>
{/snippet}

{#snippet rows()}
  <!-- svelte-ignore a11y_no_noninteractive_tabindex -->
  <ul class={theme.list()} tabindex="0">
    {#each group.actors as actor (actor.id)}
      <li class={theme.row()}>
        <UserAvatar user={{ displayName: actor.displayName }} size="sm" />
        <span class={theme.name()}>{actor.displayName}</span>
      </li>
    {/each}
  </ul>
{/snippet}

{#snippet footer()}
  <div class={theme.footer()}>
    <Icon icon="ph:user" class={theme.footerIcon()} />
    {plural(group.visitorCount, ['and a visitor', 'and # visitors'])}
  </div>
{/snippet}

{#if group.actors.length > 2}
  <Popover>
    <PopoverTrigger class={theme.seeAll()} openOnHover openDelay={300}>
      <span>See all {group.actorTotal}</span>
      <Icon icon="lucide:arrow-up-right" class={theme.seeAllIcon()} />
    </PopoverTrigger>
    <PopoverContent class={theme.card()} align="start" sideOffset={8}>
      {@render header()}
      <ScrollArea maxHeight="md" orientation="vertical" type="scroll">
        {@render rows()}
      </ScrollArea>
      {#if group.visitorCount > 0}
        {@render footer()}
      {/if}
    </PopoverContent>
  </Popover>
{/if}
