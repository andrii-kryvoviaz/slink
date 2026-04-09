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
    title: 'pages.faq.questions.supported_image_formats',
    content: SupportedFormatsContent,
  },
  {
    slug: 'image-visability',
    title: 'pages.faq.questions.image_visibility',
    content: ImageVisibilityContent,
  },
  {
    slug: 'share-feature',
    title: 'pages.faq.questions.share_feature',
    content: ShareFeatureContent,
  },
  {
    slug: 'found-an-issue',
    title: 'pages.faq.questions.found_an_issue',
    content: IssueReportContent,
  },
];
