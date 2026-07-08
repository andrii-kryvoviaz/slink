import type { ToggleGroupOption } from '@slink/ui/components/toggle-group/toggle-group.types';

import type { MediaFormatOption } from '@slink/lib/settings/Type/MediaFormat';

export class MediaFormatSelection {
  private _formats: MediaFormatOption[];
  private _getMask: () => number;
  private _setMask: (mask: number) => void;

  constructor(
    formats: MediaFormatOption[],
    getMask: () => number,
    setMask: (mask: number) => void,
  ) {
    this._formats = formats;
    this._getMask = getMask;
    this._setMask = setMask;
  }

  get options(): ToggleGroupOption[] {
    return this._formats.map(({ value, label }) => ({ value, label }));
  }

  get values(): string[] {
    const mask = this._getMask();

    return this._formats
      .filter((format) => mask & format.bit)
      .map((format) => format.value);
  }

  set values(selected: string[]) {
    const mask = this._formats
      .filter((format) => selected.includes(format.value))
      .reduce((accumulated, format) => accumulated | format.bit, 0);

    this._setMask(mask);
  }
}
