import { ThemeCatalog, builtInThemes } from '@slink/lib/settings/ThemeCatalog';
import type {
  CustomTheme,
  CustomizationSettings,
  ThemeDraft,
} from '@slink/lib/settings/Type/CustomizationSettings';
import { builtInThemeLabel } from '@slink/lib/settings/themes.svelte';

import { readTheme, samePalette, uniqueThemeId } from './theme.utils';

interface ThemeRow {
  id: string;
  name: string;
  builtIn: boolean;
  hidden: boolean;
  edited: boolean;
  custom: CustomTheme | undefined;
}

export class ThemeManager {
  private readonly _snapshot: string;

  catalog = $derived.by(() => new ThemeCatalog(this._settings()));

  rows: ThemeRow[] = $derived.by(() => {
    const { themes, hiddenThemes } = this._settings();
    const ids = new Set([...builtInThemes, ...themes.map(({ id }) => id)]);

    return [...ids].map((id) => {
      const builtIn = builtInThemes.includes(id);
      const custom = themes.find((theme) => theme.id === id);

      return {
        id,
        name: custom?.name ?? builtInThemeLabel(id),
        builtIn,
        hidden: hiddenThemes.includes(id),
        edited: builtIn && custom !== undefined,
        custom,
      };
    });
  });

  isDirty = $derived.by(() => this._serialize() !== this._snapshot);

  constructor(private readonly _settings: () => CustomizationSettings) {
    this._snapshot = this._serialize();
  }

  get canRemove(): boolean {
    return this.catalog.ids.length > 1;
  }

  /** A new theme starts from the colors of the current default theme. */
  draft(): ThemeDraft {
    return {
      ...this._source(this.catalog.defaultTheme),
      id: '',
      name: '',
      css: '',
    };
  }

  edit(row: ThemeRow): ThemeDraft {
    return { ...this._source(row.id), id: row.id, name: row.name };
  }

  duplicate(row: ThemeRow): ThemeDraft {
    return {
      ...this._source(row.id),
      id: '',
      name: `${row.name} 2`.slice(0, 48),
    };
  }

  save(draft: ThemeDraft): void {
    const settings = this._settings();
    const taken = this.rows.map(({ id }) => id);
    const id = draft.id || uniqueThemeId(draft.name, taken);
    const saved = this._normalize({ ...draft, id });
    const others = settings.themes.filter((item) => item.id !== id);

    if (!saved) {
      settings.themes = others;
      return;
    }

    const exists = settings.themes.some((item) => item.id === id);

    settings.themes = exists
      ? settings.themes.map((item) => (item.id === id ? saved : item))
      : [...others, saved];
  }

  remove(row: ThemeRow): void {
    const settings = this._settings();

    if (row.builtIn) {
      settings.hiddenThemes = [...settings.hiddenThemes, row.id];
    } else {
      this._discard(row.id);
    }

    if (settings.defaultTheme === row.id) {
      settings.defaultTheme = this.catalog.ids[0];
    }
  }

  restore(row: ThemeRow): void {
    const settings = this._settings();

    settings.hiddenThemes = settings.hiddenThemes.filter((id) => id !== row.id);
  }

  revert(row: ThemeRow): void {
    this._discard(row.id);
  }

  private _discard(id: string): void {
    const settings = this._settings();

    settings.themes = settings.themes.filter((theme) => theme.id !== id);
  }

  /** The editor always gets real colors, read from the stock theme if needed. */
  private _source(id: string): ThemeDraft {
    const custom = this._settings().themes.find((theme) => theme.id === id);

    if (!custom) {
      return readTheme(id);
    }

    const snapshot = $state.snapshot(custom);
    const stock = snapshot.light && snapshot.dark ? null : readTheme(id);

    return {
      ...snapshot,
      light: snapshot.light ?? stock!.light,
      dark: snapshot.dark ?? stock!.dark,
      css: snapshot.css ?? '',
    };
  }

  /**
   * A built-in theme keeps stock colors as null, so that adding CSS, a radius
   * or a name to it leaves its colors alone. Returns null when nothing differs
   * from the stock theme, which drops the override.
   */
  private _normalize(draft: ThemeDraft): CustomTheme | null {
    const theme = { ...draft, name: draft.name.trim() };

    if (!builtInThemes.includes(theme.id)) {
      return theme;
    }

    const stock = readTheme(theme.id);
    const sameColors =
      samePalette(theme.light, stock.light) &&
      samePalette(theme.dark, stock.dark);
    const untouched =
      sameColors &&
      theme.name === builtInThemeLabel(theme.id) &&
      theme.radius === null &&
      theme.css.trim() === '';

    if (untouched) {
      return null;
    }

    return {
      ...theme,
      light: sameColors ? null : theme.light,
      dark: sameColors ? null : theme.dark,
    };
  }

  private _serialize(): string {
    const { themes, hiddenThemes, defaultTheme, themeSelectorEnabled } =
      this._settings();

    return JSON.stringify([
      themes,
      hiddenThemes,
      defaultTheme,
      themeSelectorEnabled,
    ]);
  }
}
