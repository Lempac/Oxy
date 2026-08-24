import eslint from '@eslint/js';
import { defineConfig } from 'eslint/config';
import eslintConfigPrettier from 'eslint-config-prettier';
import eslintPluginVue from 'eslint-plugin-vue';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default defineConfig([
    { ignores: ['*.d.ts', '**/coverage', '**/dist', '**/vendor/**', '**/pb_hooks/**', '**/pb_migrations/**', '**/bootstrap/**'] },
    eslint.configs.recommended,
    ...tseslint.configs.recommended,
    ...eslintPluginVue.configs['flat/recommended'],
    {
        files: ['**/*.{ts,vue}'],
        rules: {
            'vue/multi-word-component-names': 'off',
            'vue/no-reserved-component-names': 'off',
            'vue/one-component-per-file': 'off',
            '@typescript-eslint/no-explicit-any': 'error',
            '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_', varsIgnorePattern: '^_' }],
        },
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: globals.browser,
            parserOptions: {
                parser: tseslint.parser,
                tsconfigRootDir: import.meta.dirname,
                project: ['./tsconfig.json'],
                extraFileExtensions: ['.vue'],
            },
        },
    },
    eslintConfigPrettier,
]);
