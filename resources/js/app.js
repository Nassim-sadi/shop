import Aura from "@primevue/themes/aura";
import PrimeVue from "primevue/config";
import ConfirmationService from "primevue/confirmationservice";
import ToastService from "primevue/toastservice";
import { createApp } from "vue";
import App from "./App.vue";
// custom imports
import emitter from "@/plugins/emitter";
import { i18n } from "@/plugins/i18n";
import { createPinia } from "pinia";
import piniaPersist from "pinia-plugin-persist";
import router from "./router/Index";

import "swiper/css";
import "swiper/scss";
import "swiper/scss/navigation";
import "swiper/scss/pagination";

import "@/assets/styles.scss";
import "@/assets/tailwind.css";
import "@/assets/adminStyles.scss";
import "@/assets/myStyles.scss";
import { ability } from "@/plugins/ability";
import { abilitiesPlugin } from "@casl/vue";
const app = createApp(App);

app.provide("emitter", emitter);

app.use(router);

const pinia = createPinia();
pinia.use(piniaPersist);
app.use(PrimeVue, {
    ripple: true,
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: ".app-dark",
        },
    },
});

app.use(abilitiesPlugin, ability);
app.use(ToastService);
app.use(ConfirmationService);
app.use(i18n);
app.use(pinia);

app.mount("#app");
