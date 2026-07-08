import type { GlobalSettings } from '@slink/lib/settings/Type/GlobalSettings';
import type { MediaFormatOption } from '@slink/lib/settings/Type/MediaFormat';

export type SettingsResponse = GlobalSettings & {
  meta?: {
    mediaFormats?: MediaFormatOption[];
  };
};
