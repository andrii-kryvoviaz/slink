export class LatestCall {
  private _seq = 0;

  enter(): () => boolean {
    const seq = ++this._seq;
    return () => seq === this._seq;
  }

  invalidate(): void {
    this._seq += 1;
  }
}
