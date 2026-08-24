import eslint from '@eslint/js';
import eslintConfigPrettier from 'eslint-config-prettier';
import eslintPluginVue from 'eslint-plugin-vue';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    { ignores: ['*.d.ts', '**/coverage', '**/dist', '**/vendor/**', '**/pb_hooks', '**/pb_migrations'] },
    eslint.configs.recommended,
    ...tseslint.configs.recommended,
    ...eslintPluginVue.configs['flat/recommended'],
    {
        files: ['**/*.{ts,vue}'],
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
    eslintConfigPrettier
);
