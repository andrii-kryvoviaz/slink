<script lang="ts">
  import {
    DateFormatter,
    getLocalTimeZone,
    parseDate,
    today,
  } from '@internationalized/date';
  import type { DateValue } from '@internationalized/date';
  import {
    Popover,
    PopoverContent,
    PopoverTrigger,
  } from '@slink/ui/components/popover';
  import { DatePicker as DatePickerPrimitive } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui/index.js';

  interface Props {
    value?: string | null;
    placeholder?: string;
    disabled?: boolean;
    class?: string;
    id?: string;
    name?: string;
    required?: boolean;
    'aria-describedby'?: string;
    'aria-invalid'?: boolean;
    leftIcon?: Snippet<[]>;
  }

  let {
    value = $bindable(),
    placeholder = 'Pick a date',
    disabled = false,
    class: className,
    id,
    name,
    required = false,
    'aria-describedby': ariaDescribedby,
    'aria-invalid': ariaInvalid,
    leftIcon,
    ...restProps
  }: Props = $props();

  let open = $state(false);
  let calendarValue: DateValue | undefined = $state(
    value ? parseDate(value.split('T')[0]) : undefined,
  );

  const df = new DateFormatter('en-US', {
    dateStyle: 'medium',
  });

  const minValue = today(getLocalTimeZone());

  $effect(() => {
    if (calendarValue) {
      const year = calendarValue.year;
      const month = calendarValue.month.toString().padStart(2, '0');
      const day = calendarValue.day.toString().padStart(2, '0');
      value = `${year}-${month}-${day}T00:00:00`;
    } else {
      value = null;
    }
  });
</script>

<Popover bind:open>
  <PopoverTrigger>
    <button
      type="button"
      class={cn(
        'border-input bg-background selection:bg-primary dark:bg-input/30 selection:text-primary-foreground ring-offset-background placeholder:text-muted-foreground shadow-xs flex h-9 w-full min-w-0 rounded-md border px-3 py-1 text-base outline-none transition-[color,box-shadow] disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
        'justify-start items-center text-left font-normal cursor-pointer hover:bg-blue-50/50 dark:hover:bg-blue-900/10',
        !calendarValue && 'text-muted-foreground',
        className,
      )}
      {disabled}
      {id}
      aria-describedby={ariaDescribedby}
      aria-invalid={ariaInvalid}
      {...restProps}
    >
      {#if leftIcon}
        <span class="mr-2 text-gray-400 flex-shrink-0">
          {@render leftIcon()}
        </span>
      {:else}
        <Icon
          icon="lucide:calendar"
          class="mr-2 h-4 w-4 text-gray-400 flex-shrink-0"
        />
      {/if}
      <span class="truncate">
        {calendarValue
          ? df.format(calendarValue.toDate(getLocalTimeZone()))
          : placeholder}
      </span>
    </button>
  </PopoverTrigger>
  <PopoverContent class="w-auto p-0" align="start">
    <DatePickerPrimitive.Root
      bind:value={calendarValue}
      weekdayFormat="short"
      fixedWeeks={true}
      {disabled}
      {minValue}
      onValueChange={() => {
        open = false;
      }}
    >
      <DatePickerPrimitive.Calendar
        class="bg-background p-3 border border-border rounded-md shadow-md"
      >
        {#snippet children({ months, weekdays })}
          <DatePickerPrimitive.Header
            class="flex items-center justify-between mb-4"
          >
            <DatePickerPrimitive.PrevButton
              class={cn(
                'inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors',
                'hover:bg-muted focus:bg-muted outline-none',
              )}
            >
              <Icon icon="lucide:chevron-left" class="h-4 w-4" />
            </DatePickerPrimitive.PrevButton>
            <DatePickerPrimitive.Heading class="text-sm font-medium" />
            <DatePickerPrimitive.NextButton
              class={cn(
                'inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors',
                'hover:bg-muted focus:bg-muted outline-none',
              )}
            >
              <Icon icon="lucide:chevron-right" class="h-4 w-4" />
            </DatePickerPrimitive.NextButton>
          </DatePickerPrimitive.Header>

          {#each months as month (month.value)}
            <DatePickerPrimitive.Grid
              class="w-full border-collapse select-none space-y-1"
            >
              <DatePickerPrimitive.GridHead>
                <DatePickerPrimitive.GridRow
                  class="mb-2 flex w-full justify-between"
                >
                  {#each weekdays as day (day)}
                    <DatePickerPrimitive.HeadCell
                      class="text-muted-foreground font-medium w-8 rounded-md text-xs text-center"
                    >
                      <div>{day.slice(0, 2)}</div>
                    </DatePickerPrimitive.HeadCell>
                  {/each}
                </DatePickerPrimitive.GridRow>
              </DatePickerPrimitive.GridHead>
              <DatePickerPrimitive.GridBody>
                {#each month.weeks as weekDates (weekDates)}
                  <DatePickerPrimitive.GridRow class="flex w-full">
                    {#each weekDates as date (date)}
                      <DatePickerPrimitive.Cell
                        {date}
                        month={month.value}
                        class="p-0 relative h-8 w-8 text-center text-sm"
                      >
                        <DatePickerPrimitive.Day
                          class={cn(
                            'relative inline-flex h-8 w-8 items-center justify-center whitespace-nowrap rounded-md text-sm font-normal transition-colors',
                            'hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-900 dark:hover:text-blue-200',
                            'data-[selected]:bg-primary data-[selected]:text-primary-foreground data-[selected]:hover:bg-primary data-[selected]:hover:text-primary-foreground',
                            'data-[today]:bg-blue-100 dark:data-[today]:bg-blue-900/30 data-[today]:text-blue-900 dark:data-[today]:text-blue-200',
                            'data-[outside-month]:text-muted-foreground data-[outside-month]:opacity-50',
                            'data-[disabled]:text-muted-foreground data-[disabled]:opacity-50 data-[disabled]:cursor-not-allowed',
                            'data-[unavailable]:line-through',
                          )}
                        >
                          {date.day}
                        </DatePickerPrimitive.Day>
                      </DatePickerPrimitive.Cell>
                    {/each}
                  </DatePickerPrimitive.GridRow>
                {/each}
              </DatePickerPrimitive.GridBody>
            </DatePickerPrimitive.Grid>
          {/each}
        {/snippet}
      </DatePickerPrimitive.Calendar>
    </DatePickerPrimitive.Root>
  </PopoverContent>
</Popover>

{#if name}
  <input type="hidden" {name} {value} />
{/if}
