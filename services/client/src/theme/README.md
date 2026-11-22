# Theming

Themes are managed by admins in **Admin → Settings → Customization**. Each theme has its own **Custom CSS**, set in the theme editor. It applies only to users whose active theme is that theme, and only while it is active. It is unlayered and loads last, so it overrides every token and utility without `!important`.

## Contract

Custom CSS should rely only on the following. Tailwind classes and markup may change at any time.

- **Colors**: `--color-background`, `--color-foreground`, `--color-primary`, `--color-accent`, `--color-success`, `--color-warning`, `--color-danger`, `--color-info`. Every other color is derived from these in `core/` and can be overridden individually. Use `light-dark(a, b)` to set both modes.
- **Shape and type**: `--radius`, `--font-sans`, `--font-mono`, `--spacing`, `--text-*`, `--blur-*`.
- **Brand buttons**: `--color-brand-*`, `--color-on-brand`.
- **Active theme**: `[data-theme='<id>']` on `<html>`, or `[data-theme='edited:<id>']` for a built-in theme whose colors were changed. Adding only CSS, a radius or a name to a built-in theme keeps its plain id.
- **Mode**: `.light`, `.dark` or `.system` on `<html>`.
- **Parts**: `[data-slot='<name>']`, for example `app`, `app-header`, `app-content`, `app-footer`, `navbar`, `brand-logo`, `button`, `notice`, `banner`, `setting-item`, `explore-card`, `collection-card`.

Paste this into the Custom CSS of one theme. It loads only while that theme is active:

```css
:root {
  --radius: 0;
  --font-sans: 'Fira Sans', sans-serif;
  --color-accent: light-dark(#7c3aed, #a78bfa);
}

[data-slot='brand-logo'] {
  height: 3rem;
}
```

Append `?custom-css=off` to any address to load it without theme CSS. Use it to recover from CSS that breaks a page.

## Adding a built-in theme

Add the preset to `presets/`, import it in `index.css`, and register its id in `Theme` (client) and `DisplayTheme` (API), and its label in `src/lib/settings/themes.svelte.ts`. A preset sets all eight base colors.
