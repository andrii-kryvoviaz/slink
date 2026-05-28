import type { AlertType } from '@slink/ui/components/markdown-alert';

export type MarkdownNode =
  | { kind: 'html'; html: string }
  | { kind: 'alert'; type: AlertType; content: string };

export type MarkdownTransformer = (nodes: MarkdownNode[]) => MarkdownNode[];
