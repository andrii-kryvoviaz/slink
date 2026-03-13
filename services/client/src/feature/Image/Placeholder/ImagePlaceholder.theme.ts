import { cva } from 'class-variance-authority';

export const imagePlaceholderVariants = cva('border-none', {
  variants: {
    objectFit: {
      contain: 'object-contain',
      fill: 'object-fill',
      cover: 'object-cover',
      none: '',
    },
    keepAspectRatio: {
      true: 'w-full h-full',
      false: '',
    },
    visibility: {
      visible: '',
      hidden: 'invisible absolute',
    },
  },
  compoundVariants: [
    { keepAspectRatio: false, objectFit: 'contain', class: 'w-full h-full' },
    { keepAspectRatio: false, objectFit: 'fill', class: 'w-full h-full' },
    { keepAspectRatio: false, objectFit: 'cover', class: 'w-full h-full' },
    { keepAspectRatio: false, objectFit: 'none', class: 'w-full h-full' },
  ],
  defaultVariants: {
    objectFit: 'none',
    keepAspectRatio: true,
    visibility: 'visible',
  },
});

export const imagePlaceholderWrapperVariants = cva(
  'group relative flex items-center justify-center overflow-hidden border-slate-500/10 bg-white/0 @container',
  {
    variants: {
      keepAspectRatio: {
        true: 'w-full',
        false: '',
      },
      objectFit: {
        contain: '',
        fill: '',
        cover: '',
        none: '',
      },
      rounded: {
        true: 'rounded-md',
        false: '',
      },
      bordered: {
        true: 'border',
        false: '',
      },
    },
    compoundVariants: [
      {
        keepAspectRatio: false,
        objectFit: 'contain',
        class: '',
      },
      {
        keepAspectRatio: false,
        objectFit: 'fill',
        class: 'w-full h-full',
      },
      {
        keepAspectRatio: false,
        objectFit: 'cover',
        class: 'w-full h-full',
      },
      {
        keepAspectRatio: false,
        objectFit: 'none',
        class: 'w-full h-full',
      },
    ],
    defaultVariants: {
      keepAspectRatio: true,
      objectFit: 'none',
      rounded: false,
      bordered: false,
    },
  },
);
