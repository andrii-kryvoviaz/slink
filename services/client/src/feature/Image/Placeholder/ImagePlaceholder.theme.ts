import { cva } from 'class-variance-authority';

export const imagePlaceholderVariants = cva('border-none', {
  variants: {
    stretch: {
      true: 'w-full h-full',
      false: '',
    },
    objectFit: {
      contain: 'object-contain',
      fill: 'object-fill',
      none: '',
    },
    visibility: {
      visible: '',
      hidden: 'invisible absolute',
    },
  },
  defaultVariants: {
    stretch: false,
    objectFit: 'none',
    visibility: 'visible',
  },
});
