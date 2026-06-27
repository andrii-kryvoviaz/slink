export interface PickerCreate {
  instant?: (name: string) => Promise<void>;
  detailed?: (name: string) => void;
}
