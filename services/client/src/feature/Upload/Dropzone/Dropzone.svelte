<script lang="ts">
  import { twMerge } from 'tailwind-merge';

  import type {
    HTMLButtonAttributes,
    HTMLInputAttributes,
  } from 'svelte/elements';

  type ButtonEvents = Pick<
    HTMLButtonAttributes,
    | 'onfocus'
    | 'onblur'
    | 'onmouseenter'
    | 'onmouseleave'
    | 'onmouseover'
    | 'ondragenter'
    | 'ondragleave'
    | 'ondragover'
    | 'ondrop'
  >;

  type ExcludeButtonEvents<T> = Omit<T, keyof ButtonEvents>;

  interface Props
    extends ExcludeButtonEvents<HTMLInputAttributes>, ButtonEvents {
    class?: string;
    value?: string;
    files?: FileList;
    multiple?: boolean;
  }

  let input: HTMLInputElement | undefined = $state();

  let {
    value = $bindable(),
    files = $bindable(),
    multiple = false,
    onchange,
    onclick,
    onfocus,
    onblur,
    onmouseenter,
    onmouseleave,
    onmouseover,
    ondragenter,
    ondragleave,
    ondragover,
    ondrop,
    children,
    ...props
  }: Props = $props();

  function keydown(ev: KeyboardEvent) {
    if (!input) return;

    if ([' ', 'Enter'].includes(ev.key)) {
      ev.preventDefault();
      input.click();
    }
  }

  function onClick(event: MouseEvent) {
    if (!input) return;

    event.preventDefault();
    input.click();
  }

  let defaultClass: string =
    'relative overflow-hidden flex flex-col justify-center items-center w-full bg-card-primary cursor-pointer hover:bg-card-secondary';
</script>

<button
  class={twMerge(defaultClass, props.class)}
  onkeydown={keydown}
  onclick={onClick}
  {onfocus}
  {onblur}
  {onmouseenter}
  {onmouseleave}
  {onmouseover}
  {ondragenter}
  {ondragleave}
  {ondragover}
  {ondrop}
  type="button"
>
  {@render children?.()}
</button>
<label class="hidden">
  <input
    {...props}
    bind:value
    bind:files
    bind:this={input}
    {onchange}
    {onclick}
    {multiple}
    class="absolute inset-0 block h-full w-full cursor-pointer opacity-0"
    type="file"
    accept="image/*"
  />
</label>
