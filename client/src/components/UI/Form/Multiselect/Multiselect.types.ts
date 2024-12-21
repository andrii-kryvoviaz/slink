export type MultiselectType = 'regular' | 'bitmask';

export type MultiselectValue<T extends MultiselectType> = T extends 'regular'
  ? string[]
  : number;

export type MultiselectItemData = {
  key: string;
  name: string;
};

export type MultiselectContext = {
  onRegister: (item: MultiselectItemData) => void;
  onSelect: (item: MultiselectItemData) => void;
};
