<script lang="ts">
  import { Button, type ButtonProps } from '@slink/ui/components/button';

  interface Props extends Omit<ButtonProps, 'onclick'> {
    accept?: string;
    onPick: (file: File) => void;
  }

  let { accept, onPick, children, ...rest }: Props = $props();

  let fileInput: HTMLInputElement | null = null;

  const handleChange = (event: Event) => {
    const input = event.currentTarget as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';

    if (!file) {
      return;
    }

    onPick(file);
  };
</script>

<Button {...rest} onclick={() => fileInput?.click()}>
  {@render children?.()}
</Button>
<input
  bind:this={fileInput}
  type="file"
  {accept}
  class="hidden"
  onchange={handleChange}
/>
