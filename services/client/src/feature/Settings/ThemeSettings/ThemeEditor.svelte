<script lang="ts">
  import { Dialog, Modal } from '@slink/ui/components/dialog';
  import { Input } from '@slink/ui/components/input';
  import { Label } from '@slink/ui/components/label';

  import Icon from '@iconify/svelte';

  import {
    type ThemeColor,
    type ThemeDraft,
    themeColors,
  } from '@slink/lib/settings/Type/CustomizationSettings';

  import { isHexColor } from './theme.utils';

  interface Props {
    theme: ThemeDraft;
    onSave: (theme: ThemeDraft) => void;
    onClose: () => void;
  }

  let { theme, onSave, onClose }: Props = $props();

  const schemes = ['light', 'dark'] as const;

  let draft = $state($state.snapshot(theme));
  let radius = $state(theme.radius?.toString() ?? '');

  const isNew = theme.id === '';

  const colorLabels: Record<ThemeColor, () => string> = {
    background: () => 'Background',
    foreground: () => 'Text',
    primary: () => 'Primary',
    accent: () => 'Accent',
    success: () => 'Success',
    warning: () => 'Warning',
    danger: () => 'Danger',
    info: () => 'Info',
  };

  const schemeLabels = {
    light: () => 'Light',
    dark: () => 'Dark',
  };

  const radiusValue = $derived(radius.trim() === '' ? null : Number(radius));

  const isRadiusValid = $derived(
    radiusValue === null || (radiusValue >= 0 && radiusValue <= 2),
  );

  const isValid = $derived(
    draft.name.trim() !== '' &&
      isRadiusValid &&
      schemes.every((scheme) =>
        themeColors.every((color) => isHexColor(draft[scheme][color])),
      ),
  );

  const handleSubmit = (event: SubmitEvent) => {
    event.preventDefault();

    if (!isValid) {
      return;
    }

    onSave({
      ...$state.snapshot(draft),
      name: draft.name.trim(),
      radius: radiusValue,
    });
  };
</script>

<Dialog
  open
  onOpenChange={(open) => !open && onClose()}
  size="lg"
  variant="blue"
  class="max-h-[calc(100dvh-2rem)] overflow-y-auto"
>
  <div class="space-y-6" data-slot="theme-editor">
    <Modal.Header variant="blue">
      {#snippet icon()}
        <Icon icon="ph:palette" />
      {/snippet}
      {#snippet title()}
        {#if isNew}
          Add Theme
        {:else}
          Edit Theme
        {/if}
      {/snippet}
      {#snippet description()}
        Every other shade is derived from these colors
      {/snippet}
    </Modal.Header>

    <form onsubmit={handleSubmit} class="space-y-6">
      <div class="grid gap-4 sm:grid-cols-2">
        <Input
          id="theme-name"
          name="themeName"
          label="Theme Name"
          bind:value={draft.name}
          placeholder="e.g., Ocean"
          maxlength={48}
          size="md"
          rounded="lg"
          required
        />
        <Input
          id="theme-radius"
          name="themeRadius"
          label="Corner Radius (rem)"
          bind:value={radius}
          placeholder="0.625"
          inputmode="decimal"
          size="md"
          rounded="lg"
          error={isRadiusValid ? undefined : 'Enter a number from 0 to 2'}
        />
      </div>

      <div
        class="grid grid-cols-2 items-center sm:grid-cols-[minmax(0,1fr)_minmax(0,1.5fr)_minmax(0,1.5fr)] gap-x-3 gap-y-2"
      >
        <span class="hidden sm:block"></span>
        {#each schemes as scheme (scheme)}
          <span class="text-xs font-medium text-foreground-muted">
            {schemeLabels[scheme]()}
          </span>
        {/each}

        {#each themeColors as color (color)}
          <Label class="col-span-2 sm:col-span-1">{colorLabels[color]()}</Label>
          {#each schemes as scheme (scheme)}
            {@const label = `${colorLabels[color]()} (${schemeLabels[scheme]()})`}
            <div class="flex items-center gap-2">
              <input
                type="color"
                aria-label={`Pick ${label}`}
                name={`${scheme}-${color}-picker`}
                value={isHexColor(draft[scheme][color])
                  ? draft[scheme][color]
                  : '#000000'}
                oninput={(event) =>
                  (draft[scheme][color] = event.currentTarget.value)}
                class="h-9 w-9 shrink-0 cursor-pointer rounded-md border border-border bg-background p-0.5 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
              />
              <input
                type="text"
                aria-label={label}
                name={`${scheme}-${color}`}
                bind:value={draft[scheme][color]}
                maxlength={7}
                spellcheck="false"
                aria-invalid={isHexColor(draft[scheme][color])
                  ? undefined
                  : true}
                class="h-9 w-full min-w-0 rounded-md border border-border bg-background px-2 font-mono text-xs text-foreground outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-danger dark:bg-input/30"
              />
            </div>
          {/each}
        {/each}
      </div>

      <div class="space-y-2">
        <Label>Custom CSS</Label>
        <textarea
          name="themeCss"
          aria-label="Custom CSS"
          bind:value={draft.css}
          placeholder=":root &#123; --radius: 0; &#125;"
          maxlength={65536}
          rows={8}
          spellcheck="false"
          class="border-border bg-background dark:bg-input/30 placeholder:text-foreground-muted shadow-xs flex w-full min-w-0 resize-y rounded-lg border px-4 py-2.5 font-mono text-sm outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
        ></textarea>
        <p class="text-xs text-foreground-muted">
          Applied only while this theme is active. If it breaks a page, add
          <code class="font-mono">?custom-css=off</code> to the address to load it
          without custom CSS.
        </p>
      </div>

      <div class="grid gap-3 sm:grid-cols-2" aria-hidden="true">
        {#each schemes as scheme (scheme)}
          {@const colors = draft[scheme]}
          <div
            class="space-y-3 border border-border p-4"
            style:background-color={colors.background}
            style:color={colors.foreground}
            style:border-radius={`${isRadiusValid ? (radiusValue ?? 0.625) : 0.625}rem`}
          >
            <p class="text-sm font-semibold">
              {draft.name}&nbsp;
            </p>
            <div class="flex gap-1.5">
              {#each themeColors.slice(2) as color (color)}
                <span
                  class="h-5 flex-1"
                  style:background-color={colors[color]}
                  style:border-radius={`${isRadiusValid ? (radiusValue ?? 0.625) / 2 : 0.3125}rem`}
                ></span>
              {/each}
            </div>
          </div>
        {/each}
      </div>

      <Modal.Footer
        variant="blue"
        submitText={isNew ? 'Add Theme' : 'Apply Changes'}
        submitDisabled={!isValid}
        onCancel={onClose}
      />
    </form>
  </div>
</Dialog>
