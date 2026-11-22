import { ThemeCatalog, builtInThemes } from '@slink/lib/settings/ThemeCatalog';
import type {
  CustomTheme,
  CustomizationSettings,
} from '@slink/lib/settings/Type/CustomizationSettings';
import { builtInThemeLabel } from '@slink/lib/settings/themes.svelte';

import { readTheme, uniqueThemeId } from './theme.utils';

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
  draft(): CustomTheme {
    return { ...this._source(this.catalog.defaultTheme), id: '', name: '' };
  }

  edit(row: ThemeRow): CustomTheme {
    return { ...this._source(row.id), id: row.id, name: row.name };
  }

  duplicate(row: ThemeRow): CustomTheme {
    return {
      ...this._source(row.id),
      id: '',
      name: `${row.name} 2`.slice(0, 48),
    };
  }

  save(theme: CustomTheme): void {
    const settings = this._settings();
    const taken = this.rows.map(({ id }) => id);
    const saved = {
      ...theme,
      id: theme.id || uniqueThemeId(theme.name, taken),
    };
    const exists = settings.themes.some(({ id }) => id === saved.id);

    settings.themes = exists
      ? settings.themes.map((item) => (item.id === saved.id ? saved : item))
      : [...settings.themes, saved];
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

  private _source(id: string): CustomTheme {
    const custom = this._settings().themes.find((theme) => theme.id === id);

    if (custom) {
      return $state.snapshot(custom);
    }

    return readTheme(id);
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
