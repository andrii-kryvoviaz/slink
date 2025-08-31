<script lang="ts">
  import {
    DateFormatter,
    type DateValue,
    getLocalTimeZone,
    parseDate,
    today,
  } from '@internationalized/date';
  import { DatePicker } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  interface Props {
    value?: string | null;
    label?: string;
    placeholder?: string;
    error?: string;
    disabled?: boolean;
    leftIcon?: Snippet<[]>;
    class?: string;
    id?: string;
    required?: boolean;
  }

  let {
    value = $bindable(),
    label,
    placeholder = 'Select a date',
    error = undefined,
    disabled = false,
    leftIcon,
    class: classNameProp = '',
    id,
    required = false,
  }: Props = $props();

  let calendarValue: DateValue | undefined = $state(
    value ? parseDate(value.split('T')[0]) : undefined,
  );

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

<div class="relative">
  <DatePicker.Root
    bind:value={calendarValue}
    weekdayFormat="short"
    fixedWeeks={true}
    {disabled}
    {minValue}
  >
    <div class="flex w-full flex-col gap-1.5">
      {#if label}
        <DatePicker.Label
          class="block select-none text-sm font-medium text-gray-700 dark:text-gray-300"
        >
          {label}
          {#if required}
            <span class="text-red-500 ml-1">*</span>
          {/if}
        </DatePicker.Label>
      {/if}

      <DatePicker.Input
        class={className(
          'h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100',
          'focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20',
          'hover:border-gray-300 dark:hover:border-gray-600',
          'flex w-full select-none items-center px-3 py-2 text-sm tracking-[0.01em] transition-all duration-200',
          'disabled:cursor-not-allowed disabled:opacity-50',
          error &&
            'border-red-500 focus-within:border-red-500 focus-within:ring-red-500/20',
          classNameProp,
        )}
      >
        {#snippet children({ segments })}
          {#if leftIcon}
            <span class="mr-2 text-gray-400">
              {@render leftIcon()}
            </span>
          {/if}

          {#each segments as { part, value }, i (part + i)}
            <div class="inline-block select-none">
              {#if part === 'literal'}
                <DatePicker.Segment
                  {part}
                  class="text-gray-500 dark:text-gray-400 p-1"
                >
                  {value}
                </DatePicker.Segment>
              {:else}
                <DatePicker.Segment
                  {part}
                  class="rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-900 dark:focus:text-gray-100 aria-[valuetext=Empty]:text-gray-400 dark:aria-[valuetext=Empty]:text-gray-500 focus-visible:ring-0 focus-visible:ring-offset-0 px-1 py-1"
                >
                  {value}
                </DatePicker.Segment>
              {/if}
            </div>
          {/each}

          <DatePicker.Trigger
            class="text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 dark:active:bg-gray-600 ml-auto inline-flex h-8 w-8 items-center justify-center rounded-md transition-all"
          >
            <Icon icon="lucide:calendar" class="h-4 w-4" />
          </DatePicker.Trigger>
        {/snippet}
      </DatePicker.Input>

      <DatePicker.Content sideOffset={6} class="z-50">
        <DatePicker.Calendar
          class="border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-4"
        >
          {#snippet children({ months, weekdays })}
            <DatePicker.Header class="flex items-center justify-between mb-4">
              <DatePicker.PrevButton
                class="rounded-md bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 inline-flex h-8 w-8 items-center justify-center transition-all active:scale-[0.98] border border-gray-200 dark:border-gray-700"
              >
                <Icon icon="lucide:chevron-left" class="h-4 w-4" />
              </DatePicker.PrevButton>
              <DatePicker.Heading
                class="text-sm font-semibold text-gray-900 dark:text-gray-100"
              />
              <DatePicker.NextButton
                class="rounded-md bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 inline-flex h-8 w-8 items-center justify-center transition-all active:scale-[0.98] border border-gray-200 dark:border-gray-700"
              >
                <Icon icon="lucide:chevron-right" class="h-4 w-4" />
              </DatePicker.NextButton>
            </DatePicker.Header>

            <div class="flex flex-col space-y-4">
              {#each months as month (month.value)}
                <DatePicker.Grid
                  class="w-full border-collapse select-none space-y-1"
                >
                  <DatePicker.GridHead>
                    <DatePicker.GridRow
                      class="mb-2 flex w-full justify-between"
                    >
                      {#each weekdays as day (day)}
                        <DatePicker.HeadCell
                          class="text-gray-500 dark:text-gray-400 font-medium w-8 rounded-md text-xs text-center"
                        >
                          <div>{day.slice(0, 2)}</div>
                        </DatePicker.HeadCell>
                      {/each}
                    </DatePicker.GridRow>
                  </DatePicker.GridHead>
                  <DatePicker.GridBody>
                    {#each month.weeks as weekDates (weekDates)}
                      <DatePicker.GridRow class="flex w-full">
                        {#each weekDates as date (date)}
                          <DatePicker.Cell
                            {date}
                            month={month.value}
                            class="p-0 relative h-8 w-8 text-center text-sm"
                          >
                            <DatePicker.Day
                              class="rounded-md text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 data-[selected]:bg-blue-500 data-[selected]:text-white data-[selected]:hover:bg-blue-600 data-[disabled]:text-gray-400 dark:data-[disabled]:text-gray-500 data-[disabled]:pointer-events-none data-[outside-month]:pointer-events-none data-[outside-month]:text-gray-400 dark:data-[outside-month]:text-gray-500 data-[selected]:font-medium data-[unavailable]:line-through group relative inline-flex h-8 w-8 items-center justify-center whitespace-nowrap border border-transparent bg-transparent p-0 text-sm font-normal transition-all"
                            >
                              <div
                                class="bg-blue-500 group-data-[selected]:bg-white group-data-[today]:block absolute top-1 hidden h-1 w-1 rounded-full transition-all"
                              ></div>
                              {date.day}
                            </DatePicker.Day>
                          </DatePicker.Cell>
                        {/each}
                      </DatePicker.GridRow>
                    {/each}
                  </DatePicker.GridBody>
                </DatePicker.Grid>
              {/each}
            </div>
          {/snippet}
        </DatePicker.Calendar>
      </DatePicker.Content>
    </div>
  </DatePicker.Root>

  {#if error}
    <p class="mt-1 text-sm text-red-600 dark:text-red-400">
      {error}
    </p>
  {/if}
</div>
