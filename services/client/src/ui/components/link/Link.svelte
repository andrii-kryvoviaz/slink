<script lang="ts">
  import type { Snippet } from 'svelte';

  import { goto } from '$app/navigation';
  import type { HTMLAnchorAttributes } from 'svelte/elements';

  interface Props extends HTMLAnchorAttributes {
    href: string;
    children: Snippet;
  }

  let { href, children, class: className, ...rest }: Props = $props();

  const handleClick = (e: MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    goto(href);
  };
</script>

<a {href} class={className} onclick={handleClick} {...rest}>
  {@render children()}
</a>
