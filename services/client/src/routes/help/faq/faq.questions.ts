import type { Component } from 'svelte';

import { localize } from '$lib/utils/i18n';

import {
  ImageVisibilityContent,
  IssueReportContent,
  ShareFeatureContent,
  SupportedFormatsContent,
} from './snippets';

export interface FaqQuestion {
  slug: string;
  title: string;
  content: Component;
}

export const faqQuestions = (): FaqQuestion[] => [
  {
    slug: 'supported-image-formats',
    title: localize('What image formats Slink supports?'),
    content: SupportedFormatsContent,
  },
  {
    slug: 'image-visability',
    title: localize('What is the visibility of my images?'),
    content: ImageVisibilityContent,
  },
  {
    slug: 'share-feature',
    title: localize('Can I share my images with others?'),
    content: ShareFeatureContent,
  },
  {
    slug: 'found-an-issue',
    title: localize('I found an issue, how can I report it?'),
    content: IssueReportContent,
  },
];
