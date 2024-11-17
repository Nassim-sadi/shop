import globals from "globals";
import pluginJs from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import prettierConfig from "eslint-config-prettier";

/** @type {import('eslint').Linter.Config[]} */
export default [
    { files: ["**/*.{js,mjs,cjs,vue}"] },
    { languageOptions: { globals: { ...globals.browser, ...globals.node } } },
    pluginJs.configs.recommended,
    ...pluginVue.configs["flat/strongly-recommended"],
    prettierConfig, // Prettier overrides should come last
    {
        files: ["**/*.{js,mjs,cjs}"], // Scope JS-specific rules
        rules: {
            "no-unused-vars": [
                "error",
                { vars: "all", args: "after-used", ignoreRestSiblings: true },
            ],
        },
    },
    {
        rules: {
            "vue/multi-word-component-names": "off",
        },
    },
];
