import type { VariantProps } from 'class-variance-authority';

import type { ErrorList } from '@slink/api/Exceptions';

import type { InputTheme } from '@slink/components/UI/Form/Input/Input.theme';

export type InputProps = VariantProps<typeof InputTheme> & {
  error?: string | ErrorList;
};

export type InputVariant = InputProps['variant'];
