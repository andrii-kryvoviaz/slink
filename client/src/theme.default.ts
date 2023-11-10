import type { Theme } from 'tailwindcss-theme-cutomizer';
import type { ThemeConfig } from 'tailwindcss/types/config';

export const defaultTheme: Theme = {
  variables: {},
  variants: {
    dark: {
      bgMainFrom: 'slate.900',
      bgMainTo: 'black',
      colorTextPrimary: 'gray.200',
      colorTextSecondary: 'gray.400',
      colorAccent: 'violet.800',
      borderDropzonePrimary: 'slate.800',
      borderDropzoneSecondary: 'slate.700',
      bgCardPrimary: 'slate.900',
      bgCardSecondary: 'slate.800',
      bgButtonAccent: 'blue.500',
      borderButtonAccent: 'blue.500',
      textButtonAccent: 'white',
      textButtonDefault: 'gray.400',
      borderButtonDefault: 'zinc.800',
      bgButtonDefaultHover: 'gray.700',
      textButtonDefaultHover: 'gray.200',
    },
  },
};

export const tailwindcssTheme: Partial<ThemeConfig> = injectAlphaPlaceholder({
  backgroundColor: {
    card: {
      primary: 'var(--bg-card-primary)',
      secondary: 'var(--bg-card-secondary)',
    },
    button: {
      hover: {
        default: 'var(--bg-button-default-hover)',
      },
      accent: 'var(--bg-button-accent)',
    },
  },
  borderColor: {
    dropzone: {
      primary: 'var(--border-dropzone-primary)',
      secondary: 'var(--border-dropzone-secondary)',
    },
    button: {
      default: 'var(--border-button-default)',
      accent: 'var(--border-button-accent)',
    },
  },
  textColor: {
    color: {
      primary: 'var(--color-text-primary)',
      secondary: 'var(--color-text-secondary)',
      disabled: 'var(--color-text-disabled)',
      accent: 'var(--color-accent)',
    },
    button: {
      hover: {
        default: 'var(--text-button-default-hover)',
      },
      default: 'var(--text-button-default)',
      accent: 'var(--text-button-accent)',
    },
  },
  gradientColorStops: {
    bg: {
      start: 'var(--bg-main-from)',
      end: 'var(--bg-main-to)',
    },
  },
});

// go recursivelly through the object and replace the values
// starting with 'var(--var-name)' with rgb(var(--var-name) / <alpha-value>)
function injectAlphaPlaceholder(object: any): any {
  return Object.keys(object).reduce(
    (newObj: any, key: string) => {
      const value = object[key];
      if (typeof value === 'string') {
        newObj[key] = value.replace(
          /^var\(--(.+?)\)$/,
          'rgb(var(--$1) / <alpha-value>)'
        );
      } else if (typeof value === 'object' && value !== null) {
        newObj[key] = injectAlphaPlaceholder(value); // Recurse for nested objects
      } else {
        newObj[key] = value;
      }
      return newObj;
    },
    Array.isArray(object) ? [] : {}
  );
}
