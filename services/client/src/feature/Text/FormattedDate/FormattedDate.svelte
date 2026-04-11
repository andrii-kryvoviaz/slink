<script lang="ts">
  import { getLocale } from '$lib/utils/date.svelte';

  interface Props {
    date: number;
    showTime?: boolean;
  }

  let { date, showTime = true }: Props = $props();

  let formattedDate: string = $derived.by(() => {
    const locale = getLocale();
    const dateObj = new Date(date * 1000);
    const isSameYear = dateObj.getFullYear() === new Date().getFullYear();

    const options: Intl.DateTimeFormatOptions = {
      month: 'short',
      day: 'numeric',
      ...(!isSameYear && { year: 'numeric' }),
      ...(showTime && { hour: 'numeric', minute: 'numeric' }),
    };

    return dateObj.toLocaleDateString(locale, options);
  });
</script>

<span>
  {formattedDate}
</span>
