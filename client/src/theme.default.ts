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
      colorBorderPrimary: 'slate.800',
      colorBorderSecondary: 'slate.700',
      bgCardPrimary: 'slate.900',
      bgCardSecondary: 'slate.800',
      bgButtonPrimary: 'blue.500',
    },
  },
};

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

export const tailwindcssTheme: Partial<ThemeConfig> = injectAlphaPlaceholder({
  backgroundColor: {
    card: {
      primary: 'var(--bg-card-primary)',
      secondary: 'var(--bg-card-secondary)',
    },
    button: {
      primary: 'var(--bg-button-primary)',
      secondary: 'var(--bg-button-secondary)',
      accent: 'var(--color-accent)',
    },
  },
  borderColor: {
    primary: 'var(--color-border-primary)',
    secondary: 'var(--color-border-secondary)',
    accent: 'var(--color-accent)',
  },
  textColor: {
    color: {
      primary: 'var(--color-text-primary)',
      secondary: 'var(--color-text-secondary)',
      disabled: 'var(--color-text-disabled)',
      accent: 'var(--color-accent)',
    },
  },
  gradientColorStops: {
    bg: {
      start: 'var(--bg-main-from)',
      end: 'var(--bg-main-to)',
    },
  },
});
