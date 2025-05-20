<script lang="ts">
  import { Button } from '@slink/components/UI/Action';

  interface Props {
    close: () => void;
    confirm: () => void;
    name: string;
    displayValue: string;
  }

  let { close, confirm, name, displayValue }: Props = $props();
</script>

<p class="text-left text-sm">
  Are you sure you want to set <strong>{name}</strong> to
  <strong>{displayValue}</strong>?
</p>

<div class="mt-5 flex gap-2">
  <Button variant="outline" size="sm" class="w-1/2" onclick={close}>
    Cancel
  </Button>

  <Button variant="danger" size="sm" class="w-1/2" onclick={confirm}
    >Reset & Save</Button
  >
</div>
