import { SortOrder } from '@slink/lib/enum/SortOrder';

export enum Mode {
  LIGHT = 'light',
  DARK = 'dark',
  SYSTEM = 'system',
}

export enum Theme {
  DEFAULT = 'default',
  NORD = 'nord',
  CATPPUCCIN = 'catppuccin',
  GRUVBOX = 'gruvbox',
  ROSE_PINE = 'rose-pine',
  TOKYO_NIGHT = 'tokyo-night',
  EVERFOREST = 'everforest',
  MONOCHROME = 'monochrome',
}

export enum Locale {
  EN = 'en',
  DE = 'de',
  ES = 'es',
  FR = 'fr',
  IT = 'it',
  PL = 'pl',
  UK = 'uk',
  JA = 'ja',
  ZH = 'zh',
}

export type SettingsKey =
  | 'mode'
  | 'theme'
  | 'locale'
  | 'sidebar'
  | 'navigation'
  | 'userAdmin'
  | 'table'
  | 'history'
  | 'explore'
  | 'tags'
  | 'share'
  | 'comment'
  | 'uploadOptions'
  | 'banners'
  | 'collections';

export const settingsKeys: SettingsKey[] = [
  'mode',
  'theme',
  'locale',
  'sidebar',
  'navigation',
  'userAdmin',
  'table',
  'history',
  'explore',
  'tags',
  'share',
  'comment',
  'uploadOptions',
  'banners',
  'collections',
];

const resolveEnum = <T extends Record<string, string>>(
  values: T,
  value: unknown,
  fallback: T[keyof T],
): T[keyof T] => {
  if (typeof value === 'string' && Object.values(values).includes(value)) {
    return value as T[keyof T];
  }

  return fallback;
};

export const resolveTheme = (value: unknown): Theme =>
  resolveEnum(Theme, value, Theme.DEFAULT);

export const resolveMode = (value: unknown): Mode =>
  resolveEnum(Mode, value, Mode.SYSTEM);

export const resolveLocale = (value: unknown): Locale =>
  resolveEnum(Locale, value, Locale.EN);

export type ViewMode = 'grid' | 'list' | 'table' | 'tree';

export type ViewModeSettingsKey =
  'userAdmin' | 'history' | 'explore' | 'tags' | 'collections';

export const supportedViewModes: Record<
  ViewModeSettingsKey,
  readonly ViewMode[]
> = {
  userAdmin: ['grid', 'list'],
  history: ['grid', 'list', 'table'],
  explore: ['grid', 'list'],
  tags: ['table', 'tree'],
  collections: ['grid', 'table'],
};

export const defaultViewModes: Record<ViewModeSettingsKey, ViewMode> = {
  userAdmin: 'list',
  history: 'table',
  explore: 'grid',
  tags: 'table',
  collections: 'grid',
};

export const isViewModeSettingsKey = (
  key: SettingsKey,
): key is ViewModeSettingsKey => key in supportedViewModes;

export const defaultSettings: Record<SettingsKey, unknown> = {
  mode: Mode.SYSTEM,
  theme: Theme.DEFAULT,
  locale: Locale.EN,
  sidebar: { expanded: true },
  navigation: { expandedGroups: {} },
  userAdmin: { viewMode: defaultViewModes.userAdmin },
  table: {
    users: {
      pageSize: 12,
      columnVisibility: {
        displayName: true,
        username: true,
        status: true,
        roles: true,
      },
    },
    tags: {
      pageSize: 10,
      columnVisibility: {
        name: true,
        imageCount: true,
        children: true,
      },
    },
    history: {
      pageSize: 12,
      columnVisibility: {
        fileName: true,
        mimeType: true,
        dimensions: true,
        size: true,
        isPublic: true,
        views: true,
        createdAt: true,
        tagsCollections: true,
      },
    },
    collections: {
      pageSize: 12,
      columnVisibility: {
        name: true,
        itemCount: true,
        description: true,
        createdAt: true,
      },
    },
    shares: {
      pageSize: 10,
      columnVisibility: {
        shareable: true,
        attributes: true,
        expires: true,
        createdAt: true,
      },
    },
  },
  history: { viewMode: defaultViewModes.history },
  explore: { viewMode: defaultViewModes.explore },
  tags: { viewMode: defaultViewModes.tags },
  share: { format: 'direct' },
  comment: { sortOrder: SortOrder.Asc },
  uploadOptions: { expanded: false },
  banners: { hideExifKeptNotice: false },
  collections: {
    viewMode: defaultViewModes.collections,
    pageSize: 12,
    loadStrategy: 'load_more',
  },
};
