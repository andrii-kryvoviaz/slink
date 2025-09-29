import js from '@eslint/js';
import ts from '@typescript-eslint/eslint-plugin';
import tsParser from '@typescript-eslint/parser';
import prettier from 'eslint-config-prettier';
import svelte from 'eslint-plugin-svelte';
import unusedImports from 'eslint-plugin-unused-imports';
import globals from 'globals';
import svelteParser from 'svelte-eslint-parser';

export default [
  js.configs.recommended,
  {
    files: ['**/*.{js,mjs,cjs,ts}'],
    ignores: ['**/*.svelte.ts'],
    languageOptions: {
      parser: tsParser,
      parserOptions: {
        ecmaVersion: 2022,
        sourceType: 'module',
        experimentalDecorators: true,
        emitDecoratorMetadata: true,
      },
      globals: {
        ...globals.browser,
        ...globals.node,
        ...globals.builtin,
        __APP_VERSION__: 'readonly',
        __BUILD_DATE__: 'readonly',
        __COMMIT_HASH__: 'readonly',
      },
    },
    plugins: {
      '@typescript-eslint': ts,
      'unused-imports': unusedImports,
    },
    rules: {
      'no-unused-vars': 'off',
      '@typescript-eslint/no-unused-vars': [
        'error',
        {
          argsIgnorePattern: '^_',
          varsIgnorePattern: '^_',
          ignoreRestSiblings: true,
        },
      ],
      '@typescript-eslint/no-explicit-any': 'warn',
      'unused-imports/no-unused-imports': 'error',
    },
  },
  {
    files: ['**/*.{svelte,svelte.ts}'],
    languageOptions: {
      parser: svelteParser,
      parserOptions: {
        parser: tsParser,
        experimentalDecorators: true,
        emitDecoratorMetadata: true,
      },
      globals: {
        ...globals.browser,
        ...globals.node,
        __APP_VERSION__: 'readonly',
        __BUILD_DATE__: 'readonly',
        __COMMIT_HASH__: 'readonly',
      },
    },
    plugins: {
      svelte,
      '@typescript-eslint': ts,
      'unused-imports': unusedImports,
    },
    rules: {
      'no-unused-vars': 'off',
      '@typescript-eslint/no-unused-vars': [
        'error',
        {
          argsIgnorePattern: '^_',
          varsIgnorePattern: '^_',
          ignoreRestSiblings: true,
        },
      ],
      'svelte/no-unused-svelte-ignore': 'error',
      'svelte/no-at-html-tags': 'off',
      'unused-imports/no-unused-imports': 'error',
    },
  },
  {
    ignores: [
      'build/',
      '.svelte-kit/',
      'dist/',
      'node_modules/',
      '*.config.js',
      '*.config.ts',
    ],
  },
  prettier,
];
