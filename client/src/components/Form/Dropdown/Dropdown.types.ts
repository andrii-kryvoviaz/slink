export type DropdownPosition =
  | 'bottom-left'
  | 'bottom-right'
  | 'top-left'
  | 'top-right';

export type DropdownItemData = {
  key: string;
  name: string;
};

export type DropdownContext = {
  onRegister: (item: DropdownItemData) => void;
  onSelect: (item: DropdownItemData) => void;
};

export type DropdownValue = number | string | boolean;
