export interface PickerCreate {
  instant?: (name: string) => Promise<void>;
  exists?: (name: string) => boolean;
  detailed?: (name: string) => void;
}
