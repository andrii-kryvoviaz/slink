import type { UserAvatarThemeLegacy } from '@slink/feature/User/UserAvatarLegacy/UserAvatarLegacy.theme';
import type { VariantProps } from 'class-variance-authority';

export type UserAvatarPropsLegacy = VariantProps<typeof UserAvatarThemeLegacy>;
export type UserAvatarVariantLegacy = UserAvatarPropsLegacy['variant'];
