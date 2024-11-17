<script lang="ts">
  import { Button } from '@slink/components/UI/Action';
  import { Badge } from '@slink/components/UI/Text';

  export let close: () => void;
  export let confirm: () => void;

  export let name: string;
  export let displayValue: string;
</script>

<p class="text-left text-sm">
  Are you sure you want to set <strong>{name}</strong> to <Badge
    >{displayValue}</Badge
  >?
</p>

<div class="mt-5 flex gap-2">
  <Button variant="outline" size="sm" class="w-1/2" on:click={close}>
    Cancel
  </Button>

  <Button variant="danger" size="sm" class="w-1/2" on:click={confirm}
    >Reset & Save</Button
  >
</div>
