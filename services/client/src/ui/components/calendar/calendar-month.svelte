<script lang="ts">
	import { type WithElementRef, cn } from "@slink/utils/ui/index.js";
	import type { HTMLAttributes } from "svelte/elements";

	let {
		ref = $bindable(null),
		class: className,
		children,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLElement>> = $props();
</script>

<div {...restProps} bind:this={ref} class={cn("flex flex-col", className)}>
	{@render children?.()}
</div>
