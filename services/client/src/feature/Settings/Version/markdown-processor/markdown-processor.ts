import type { MarkdownNode, MarkdownTransformer } from './types.js';

export class MarkdownProcessor {
  private _transformers: MarkdownTransformer[] = [];

  use(transformer: MarkdownTransformer): this {
    this._transformers.push(transformer);
    return this;
  }

  process(source: string): MarkdownNode[] {
    const initial: MarkdownNode[] = [{ kind: 'html', html: source }];
    return this._transformers.reduce(
      (nodes, transformer) => transformer(nodes),
      initial,
    );
  }
}
