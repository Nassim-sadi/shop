import AR from "@/locales/ar.json";
import EN from "@/locales/en.json";
import { createI18n } from "vue-i18n";

const i18n = createI18n({
    legacy: false,
    locale: import.meta.env.VITE_APP_LOCALE || "EN",
    fallbackLocale: import.meta.env.VITE_APP_FALLBACK_LOCALE || "EN",
    messages: {
        EN: EN,
        AR: AR,
    },
});
console.log(import.meta.env.VITE_APP_LOCALE);
const $t = i18n.global.t;

export { $t, i18n };
