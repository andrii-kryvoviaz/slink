<script lang="ts">
	import type { WithElementRef } from "@slink/utils/ui/index.js";
	import type { HTMLAttributes } from "svelte/elements";

	let {
		ref = $bindable(null),
		class: className,
		children,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLElement>> = $props();
</script>

<nav
	bind:this={ref}
	data-slot="breadcrumb"
	class={className}
	aria-label="breadcrumb"
	{...restProps}
>
	{@render children?.()}
</nav>
