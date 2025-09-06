<script lang="ts">
	import { Calendar as CalendarPrimitive } from "bits-ui";
	import { cn } from "@slink/utils/ui/index.js";

	let {
		ref = $bindable(null),
		class: className,
		...restProps
	}: CalendarPrimitive.GridProps = $props();
</script>

<CalendarPrimitive.Grid
	bind:ref
	class={cn("mt-4 flex w-full border-collapse flex-col gap-1", className)}
	{...restProps}
/>
