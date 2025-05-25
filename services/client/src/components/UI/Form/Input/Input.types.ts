import type { ErrorList } from '@slink/api/Exceptions';
import type { InputTheme } from '@slink/components/UI/Form/Input/Input.theme';
import type { VariantProps } from 'class-variance-authority';

export type InputProps = VariantProps<typeof InputTheme> & {
  error?: string | ErrorList;
};

export type InputVariant = InputProps['variant'];
