export class ThreadReveal {
  private _pages: number = $state(1);

  private readonly _pageSize: () => number;
  private readonly _total: () => number;

  constructor(pageSize: () => number, total: () => number) {
    this._pageSize = pageSize;
    this._total = total;
  }

  get visible(): number {
    return Math.min(this._pages * this._pageSize(), this._total());
  }

  get remaining(): number {
    return this._total() - this.visible;
  }

  get next(): number {
    return Math.min(this._pageSize(), this.remaining);
  }

  showMore(): void {
    this._pages += 1;
  }

  reset(): void {
    this._pages = 1;
  }
}
