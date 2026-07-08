import { copyText } from '$lib/utils/ui/clipboard';

import type { ShareFormat } from '@slink/lib/settings';
import { localize } from '@slink/lib/utils/i18n';

import { copyImageContent } from './copyImageContent';

export interface ShareCopySource {
  content: () => string;
  share: () => Promise<string> | string;
}

export interface ShareFormatDescriptor {
  id: ShareFormat;
  label: string;
  short: string;
  icon: string;
  copy(source: ShareCopySource, alt: string): Promise<boolean>;
}

export const shareFormats: ShareFormatDescriptor[] = [
  {
    id: 'direct',
    get label() {
      return localize('Direct Link');
    },
    get short() {
      return localize('Link');
    },
    icon: 'ph:link',
    copy: async (source) => copyText(await source.share()),
  },
  {
    id: 'markdown',
    get label() {
      return localize('Markdown');
    },
    get short() {
      return localize('MD');
    },
    icon: 'ph:markdown-logo',
    copy: async (source, alt) => copyText(`![${alt}](${await source.share()})`),
  },
  {
    id: 'bbcode',
    get label() {
      return localize('BBCode');
    },
    get short() {
      return localize('BB');
    },
    icon: 'ph:brackets-square',
    copy: async (source) => copyText(`[img]${await source.share()}[/img]`),
  },
  {
    id: 'html',
    get label() {
      return localize('HTML');
    },
    get short() {
      return localize('HTML');
    },
    icon: 'ph:code',
    copy: async (source, alt) =>
      copyText(`<img src="${await source.share()}" alt="${alt}" />`),
  },
  {
    id: 'image',
    get label() {
      return localize('Image Content');
    },
    get short() {
      return localize('Image');
    },
    icon: 'ph:image',
    copy: (source) => copyImageContent(source.content()),
  },
];

export function getShareFormat(id: ShareFormat): ShareFormatDescriptor {
  return shareFormats.find((format) => format.id === id) ?? shareFormats[0];
}
