import js from '@eslint/js';
import eslintConfigPrettier from 'eslint-config-prettier/flat';

export default [
  {
    files: ['web/themes/custom/triple_g/src/js/**/*.js'],
    languageOptions: {
      ecmaVersion: 2024,
      sourceType: 'script',
      globals: {
        Drupal: 'readonly',
        document: 'readonly',
        once: 'readonly',
        window: 'readonly',
      },
    },
    rules: {
      ...js.configs.recommended.rules,
      'no-restricted-globals': ['error', '$', 'jQuery'],
      'no-unused-vars': ['error', { argsIgnorePattern: '^settings$' }],
    },
  },
  eslintConfigPrettier,
];
