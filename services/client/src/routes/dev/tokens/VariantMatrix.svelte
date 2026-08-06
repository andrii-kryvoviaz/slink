<script lang="ts">
  import { themeFiles } from './contract';
</script>

<div class="flex flex-col gap-6">
  {#each themeFiles as file (file.label)}
    <section class="border-border/60 rounded-lg border p-3">
      <code class="text-muted-foreground font-mono text-[11px]"
        >{file.label}</code
      >
      <div class="mt-2 flex flex-col gap-3">
        {#each file.definitions as definition (definition.name)}
          {#each definition.entries as entry (entry.key)}
            <div class="flex flex-col gap-1.5">
              <code class="text-foreground/70 font-mono text-[10px]"
                >{definition.name}.{entry.key}</code
              >
              <div class="flex flex-wrap items-start gap-2">
                {#each entry.values as value (value)}
                  {#each definition.render(entry.key, value) as classes, slot (slot)}
                    <span
                      class="border-border/40 relative isolate flex h-16 w-40 items-center justify-center overflow-hidden rounded border"
                      style="contain: layout paint size;"
                    >
                      <span class={classes}>{value}</span>
                    </span>
                  {/each}
                {/each}
              </div>
            </div>
          {/each}
        {/each}
      </div>
    </section>
  {/each}
</div>
