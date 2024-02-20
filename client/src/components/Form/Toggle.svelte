<script lang="ts">
  const inputClasses = `toggle theme-controller text-toggle-default checked:text-toggle-checked [--tglbg:rgb(var(--bg-toggle))] checked:[--tglbg:rgb(var(--bg-toggle-checked))] ${$$props.class}`;
</script>

<label class="flex cursor-pointer items-center gap-2">
  <slot name="pre-icon" />
  <input {...$$props} type="checkbox" class={inputClasses} on:change />
  <slot name="post-icon" />
</label>
