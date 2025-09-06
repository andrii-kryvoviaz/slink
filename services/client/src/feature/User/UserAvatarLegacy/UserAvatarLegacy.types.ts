import type { UserAvatarTheme } from '@slink/feature/User/UserAvatar/UserAvatar.theme';
import type { VariantProps } from 'class-variance-authority';

export type UserAvatarProps = VariantProps<typeof UserAvatarTheme>;
export type UserAvatarVariant = UserAvatarProps['variant'];
