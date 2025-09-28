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
  <PopoverTrigger class="w-full">
    <button
      type="button"
      class={cn(
        'border-border bg-background selection:bg-primary dark:bg-input/30 selection:text-primary-foreground ring-offset-background placeholder:text-muted-foreground shadow-xs flex h-11 w-full min-w-0 rounded-md border px-4 py-2.5 text-sm outline-none transition-[color,box-shadow] disabled:cursor-not-allowed disabled:opacity-50',
        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] focus-visible:ring-offset-2 focus-visible:ring-offset-background',
        open && 'border-ring ring-ring/50 ring-[3px]',
        ariaInvalid &&
          'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
        'justify-start items-center text-left font-normal cursor-pointer hover:bg-input/50 dark:hover:bg-input/50',
        !calendarValue && 'text-muted-foreground',
        className,
      )}
      {disabled}
      {id}
      aria-describedby={ariaDescribedby}
      {...restProps}
    >
      {#if leftIcon}
        <span class="mr-2 text-muted-foreground flex-shrink-0">
          {@render leftIcon()}
        </span>
      {:else}
        <Icon
          icon="lucide:calendar"
          class="mr-2 h-4 w-4 text-muted-foreground flex-shrink-0"
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
        class="bg-white dark:bg-gray-900/95 text-gray-900 dark:text-gray-100 backdrop-blur-sm border border-gray-200/40 dark:border-gray-700/40 rounded-xl shadow-xl shadow-black/10 dark:shadow-black/25 p-4"
      >
        {#snippet children({ months, weekdays })}
          <DatePickerPrimitive.Header
            class="flex items-center justify-between mb-6"
          >
            <DatePickerPrimitive.PrevButton
              class={cn(
                'inline-flex h-9 w-9 items-center justify-center rounded-lg transition-colors text-muted-foreground',
                'hover:bg-gray-100 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100 focus:bg-gray-100 dark:focus:bg-gray-800/50 outline-none',
              )}
            >
              <Icon icon="lucide:chevron-left" class="h-4 w-4" />
            </DatePickerPrimitive.PrevButton>
            <DatePickerPrimitive.Heading
              class="text-sm font-semibold text-gray-900 dark:text-gray-100"
            />
            <DatePickerPrimitive.NextButton
              class={cn(
                'inline-flex h-9 w-9 items-center justify-center rounded-lg transition-colors text-muted-foreground',
                'hover:bg-gray-100 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100 focus:bg-gray-100 dark:focus:bg-gray-800/50 outline-none',
              )}
            >
              <Icon icon="lucide:chevron-right" class="h-4 w-4" />
            </DatePickerPrimitive.NextButton>
          </DatePickerPrimitive.Header>

          {#each months as month (month.value)}
            <DatePickerPrimitive.Grid
              class="w-full border-collapse select-none space-y-1.5"
            >
              <DatePickerPrimitive.GridHead>
                <DatePickerPrimitive.GridRow
                  class="mb-3 flex w-full justify-between"
                >
                  {#each weekdays as day (day)}
                    <DatePickerPrimitive.HeadCell
                      class="text-muted-foreground font-medium w-9 rounded-md text-xs text-center uppercase tracking-wider"
                    >
                      <div>{day.slice(0, 2)}</div>
                    </DatePickerPrimitive.HeadCell>
                  {/each}
                </DatePickerPrimitive.GridRow>
              </DatePickerPrimitive.GridHead>
              <DatePickerPrimitive.GridBody>
                {#each month.weeks as weekDates (weekDates)}
                  <DatePickerPrimitive.GridRow class="flex w-full gap-1">
                    {#each weekDates as date (date)}
                      <DatePickerPrimitive.Cell
                        {date}
                        month={month.value}
                        class="p-0 relative h-9 w-9 text-center text-sm"
                      >
                        <DatePickerPrimitive.Day
                          class={cn(
                            'relative inline-flex h-9 w-9 items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium transition-all duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100',
                            'data-[selected]:bg-blue-600 dark:data-[selected]:bg-blue-500 data-[selected]:text-white data-[selected]:hover:bg-blue-700 dark:data-[selected]:hover:bg-blue-600 data-[selected]:shadow-sm',
                            'data-[today]:bg-blue-50 dark:data-[today]:bg-blue-900/20 data-[today]:text-blue-600 dark:data-[today]:text-blue-400 data-[today]:font-semibold data-[today]:ring-1 data-[today]:ring-blue-200 dark:data-[today]:ring-blue-800',
                            'data-[outside-month]:text-muted-foreground data-[outside-month]:opacity-40',
                            'data-[disabled]:text-muted-foreground data-[disabled]:opacity-40 data-[disabled]:cursor-not-allowed',
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
