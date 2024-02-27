import type { VariantProps } from 'class-variance-authority';

import type { UserAvatarTheme } from '@slink/components/User/UserAvatar/UserAvatar.theme';

export type UserAvatarProps = VariantProps<typeof UserAvatarTheme>;
export type UserAvatarVariant = UserAvatarProps['variant'];
