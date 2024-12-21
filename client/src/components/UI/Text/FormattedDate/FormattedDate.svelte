<script lang="ts">
  interface Props {
    date: number;
    options?: Intl.DateTimeFormatOptions;
  }

  let {
    date,
    options = {
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: 'numeric',
    },
  }: Props = $props();

  let formattedDate: string = $derived(
    new Date(date * 1000).toLocaleDateString('en-US', options),
  );
</script>

<div>
  {formattedDate}
</div>
