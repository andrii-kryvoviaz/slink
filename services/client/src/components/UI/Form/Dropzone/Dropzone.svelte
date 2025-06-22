<script lang="ts">
  import type {
    HTMLButtonAttributes,
    HTMLInputAttributes,
  } from 'svelte/elements';
  import { twMerge } from 'tailwind-merge';

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
    extends ExcludeButtonEvents<HTMLInputAttributes>,
      ButtonEvents {
    class?: string;
    value?: string;
    files?: FileList;
  }

  let input: HTMLInputElement | undefined = $state();

  let {
    value = $bindable(),
    files = $bindable(),
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
    'flex flex-col justify-center items-center w-full bg-card-primary cursor-pointer hover:bg-card-secondary';
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
    type="file"
  />
</label>
