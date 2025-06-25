import type {
  UserDropdownContent,
  UserDropdownItem,
  UserDropdownTrigger,
} from './UserDropdown.theme';
import type { VariantProps } from 'class-variance-authority';

export type UserDropdownTriggerProps = VariantProps<typeof UserDropdownTrigger>;
export type UserDropdownContentProps = VariantProps<typeof UserDropdownContent>;
export type UserDropdownItemProps = VariantProps<typeof UserDropdownItem>;
