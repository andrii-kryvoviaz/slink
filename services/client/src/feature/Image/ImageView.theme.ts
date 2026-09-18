import { cva } from 'class-variance-authority';

export const imageCardVariants = cva(
  'group break-inside-avoid overflow-hidden rounded-lg border transition-all duration-200 hover:shadow-md dark:hover:shadow-surface-inverse/50',
  {
    variants: {
      selected: {
        true: 'ring-2 ring-primary-solid border-primary-solid',
        false: '',
      },
      border: {
        subtle: '',
        token: '',
      },
    },
    compoundVariants: [
      {
        border: 'subtle',
        class: 'bg-card dark:bg-card/60',
      },
      {
        border: 'token',
        class: 'bg-card/60',
      },
      {
        selected: false,
        border: 'subtle',
        class: 'border-foreground-subtle/25 hover:border-foreground-subtle/45',
      },
      {
        selected: false,
        border: 'token',
        class: 'border-border hover:border-border-strong',
      },
    ],
    defaultVariants: {
      selected: false,
      border: 'subtle',
    },
  },
);

export const imageListRowVariants = cva(
  'group relative flex flex-col @xl:flex-row w-full overflow-hidden rounded-lg border bg-card dark:bg-card/60 transition-all duration-200 hover:shadow-md dark:hover:shadow-surface-inverse/50',
  {
    variants: {
      selected: {
        true: 'bg-primary-solid/8 border-info-border dark:border-primary-solid ring-2 ring-primary-solid',
        false: '',
      },
      selectionMode: {
        true: 'cursor-pointer',
        false: '',
      },
    },
    compoundVariants: [
      {
        selected: false,
        class: 'border-foreground-subtle/25 hover:border-foreground-subtle/50',
      },
    ],
    defaultVariants: {
      selected: false,
      selectionMode: false,
    },
  },
);
