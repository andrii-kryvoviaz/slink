export class ImageSelectionState {
  private _selectedIds: Set<string> = $state(new Set());
  private _isSelectionMode: boolean = $state(false);

  get selectedIds(): string[] {
    return Array.from(this._selectedIds);
  }

  get selectedCount(): number {
    return this._selectedIds.size;
  }

  get isSelectionMode(): boolean {
    return this._isSelectionMode;
  }

  get hasSelection(): boolean {
    return this._selectedIds.size > 0;
  }

  enterSelectionMode(): void {
    this._isSelectionMode = true;
  }

  exitSelectionMode(): void {
    this._selectedIds = new Set();
    this._isSelectionMode = false;
  }

  toggle(id: string): void {
    const newSet = new Set(this._selectedIds);
    if (newSet.has(id)) {
      newSet.delete(id);
    } else {
      newSet.add(id);
    }
    this._selectedIds = newSet;
  }

  isSelected(id: string): boolean {
    return this._selectedIds.has(id);
  }

  selectAll(ids: string[]): void {
    this._selectedIds = new Set(ids);
  }

  deselectAll(): void {
    this._selectedIds = new Set();
  }

  reset(): void {
    this._selectedIds = new Set();
    this._isSelectionMode = false;
  }

  removeIds(ids: string[]): void {
    const newSet = new Set(this._selectedIds);
    ids.forEach((id) => newSet.delete(id));
    this._selectedIds = newSet;
  }
}

export const createImageSelectionState = (): ImageSelectionState => {
  return new ImageSelectionState();
};
