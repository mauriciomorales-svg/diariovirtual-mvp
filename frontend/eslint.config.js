const ts = require('@typescript-eslint/eslint-plugin');
const tsParser = require('@typescript-eslint/parser');

module.exports = [
    {
        files: ['src/**/*.{ts,tsx}'],
        languageOptions: {
            parser: tsParser,
            parserOptions: { project: './tsconfig.json' },
        },
        plugins: { '@typescript-eslint': ts },
        rules: {
            '@typescript-eslint/no-unused-vars': 'warn',
        },
    },
];
