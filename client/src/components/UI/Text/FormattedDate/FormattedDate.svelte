<script lang="ts">
  export let date: number;
  export let options: Intl.DateTimeFormatOptions = {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
  };

  let formattedDate: string;
  $: formattedDate = new Date(date * 1000).toLocaleDateString('en-US', options);
</script>

<div>
  {formattedDate}
</div>
