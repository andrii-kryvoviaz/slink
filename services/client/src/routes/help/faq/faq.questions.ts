import type { Component } from 'svelte';

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

export const faqQuestions: FaqQuestion[] = [
  {
    slug: 'supported-image-formats',
    title: 'What image formats Slink supports?',
    content: SupportedFormatsContent,
  },
  {
    slug: 'image-visability',
    title: 'What is the visibility of my images?',
    content: ImageVisibilityContent,
  },
  {
    slug: 'share-feature',
    title: 'Can I share my images with others?',
    content: ShareFeatureContent,
  },
  {
    slug: 'found-an-issue',
    title: 'I found an issue, how can I report it?',
    content: IssueReportContent,
  },
];
