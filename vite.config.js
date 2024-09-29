import { PrimeVueResolver } from "@primevue/auto-import-resolver";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import Components from "unplugin-vue-components/vite";
import { fileURLToPath, URL } from "url";
import { defineConfig } from "vite";

export default defineConfig({
    plugins: [
        vue(),
        Components({
            resolvers: [PrimeVueResolver()],
        }),
        laravel({
            input: ["resources/styles/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            vue: "vue/dist/vue.esm-bundler.js",
            "@": fileURLToPath(new URL("./resources/js", import.meta.url)),
        },
    },
});
