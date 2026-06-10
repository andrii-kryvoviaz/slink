import type { HookId } from './manifest.gen';

export interface HookSettings {
  order: number;
  enabled?: boolean;
}

export const hookSettings: Record<HookId, HookSettings> = {
  'request/wellKnown': { order: 10 },
  'request/responseHeaders': { order: 20 },
  'locals/cookieManager': { order: 10 },
  'locals/locale': { order: 20 },
  'locals/apiClient': { order: 30 },
  'locals/apiProxy': { order: 40 },
  'locals/globalSettings': { order: 50 },
  'locals/userPreferences': { order: 60 },
  'locals/uploadPolicy': { order: 70 },
  'locals/userLocale': { order: 80 },
  'locals/cookieSettings': { order: 90 },
  'render/theme': { order: 10 },
  'render/locale': { order: 20 },
  'response/linkHeader': { order: 10 },
  'response/htmlCache': { order: 20 },
};
