import {
  ALERT_TYPES,
  type AlertType,
} from '@slink/ui/components/markdown-alert';

import type { MarkdownNode, MarkdownTransformer } from './types.js';

const ALERT_HEADER = new RegExp(
  `^>\\s*\\[!(${ALERT_TYPES.join('|')})\\]\\s*$`,
  'i',
);
const QUOTE_LINE = /^>\s?(.*)$/;

function mapHtml(transform: (html: string) => string): MarkdownTransformer {
  return (nodes) =>
    nodes.map((node) => {
      if (node.kind !== 'html') {
        return node;
      }

      return { kind: 'html', html: transform(node.html) };
    });
}

function isBlank(line: string): boolean {
  return line.trim().length === 0;
}

function trimBlankLines(lines: string[]): string[] {
  const start = lines.findIndex((line) => !isBlank(line));
  if (start === -1) {
    return [];
  }

  let end = lines.length;
  while (isBlank(lines[end - 1])) {
    end -= 1;
  }

  return lines.slice(start, end);
}

function matchAlertType(line: string): AlertType | null {
  const match = line.match(ALERT_HEADER);
  if (!match) {
    return null;
  }

  return match[1].toLowerCase() as AlertType;
}

function matchQuoteContent(line: string): string | null {
  const match = line.match(QUOTE_LINE);
  if (!match) {
    return null;
  }

  return match[1];
}

function collectQuotedLines(
  lines: string[],
  from: number,
): { content: string[]; consumed: number } {
  const content: string[] = [];
  let cursor = from;

  while (cursor < lines.length) {
    const text = matchQuoteContent(lines[cursor]);
    if (text === null) {
      break;
    }

    content.push(text);
    cursor += 1;
  }

  return { content, consumed: cursor - from };
}

function buildHtmlNode(buffer: string[]): MarkdownNode | null {
  if (buffer.length === 0) {
    return null;
  }

  const html = buffer.join('\n');
  if (isBlank(html)) {
    return null;
  }

  return { kind: 'html', html };
}

function splitHtmlByAlerts(html: string): MarkdownNode[] {
  const lines = html.replace(/\r\n?/g, '\n').split('\n');
  const nodes: MarkdownNode[] = [];
  let buffer: string[] = [];
  let cursor = 0;

  const flushBuffer = () => {
    const node = buildHtmlNode(buffer);
    buffer = [];

    if (node !== null) {
      nodes.push(node);
    }
  };

  while (cursor < lines.length) {
    const type = matchAlertType(lines[cursor]);

    if (type === null) {
      buffer.push(lines[cursor]);
      cursor += 1;
      continue;
    }

    flushBuffer();

    const { content, consumed } = collectQuotedLines(lines, cursor + 1);
    nodes.push({
      kind: 'alert',
      type,
      content: trimBlankLines(content).join('\n'),
    });
    cursor += 1 + consumed;
  }

  flushBuffer();

  return nodes;
}

export const stripFullChangelog: MarkdownTransformer = mapHtml((html) =>
  html.replace(/\*\*Full Changelog\*\*.*$/s, ''),
);

export const extractAlerts: MarkdownTransformer = (nodes) =>
  nodes.flatMap((node) => {
    if (node.kind !== 'html') {
      return [node];
    }

    return splitHtmlByAlerts(node.html);
  });

export const formatHeadings: MarkdownTransformer = mapHtml((html) =>
  html
    .replace(
      /### (.*)/g,
      '<h3 class="text-lg font-semibold first:mt-0 mt-4 mb-2">$1</h3>',
    )
    .replace(
      /## (.*)/g,
      '<h2 class="text-xl font-bold first:mt-0 mt-6 mb-3">$1</h2>',
    )
    .replace(
      /# (.*)/g,
      '<h1 class="text-2xl font-bold first:mt-0 mt-8 mb-4">$1</h1>',
    ),
);

export const formatInlineMarkdown: MarkdownTransformer = mapHtml((html) =>
  html
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(
      /`(.*?)`/g,
      '<code class="bg-muted px-1 py-0.5 rounded text-sm">$1</code>',
    )
    .replace(/- (.*)/g, '<li class="ml-4 my-2">$1</li>'),
);
