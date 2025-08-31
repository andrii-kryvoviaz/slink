import type { InputTheme } from '@slink/legacy/UI/Form/Input/Input.theme';
import type { VariantProps } from 'class-variance-authority';

import type { ErrorList } from '@slink/api/Exceptions';

export type InputProps = VariantProps<typeof InputTheme> & {
  error?: string | ErrorList;
};

export type InputVariant = InputProps['variant'];
