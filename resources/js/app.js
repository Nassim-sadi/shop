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
import { PerfectScrollbarPlugin } from "vue3-perfect-scrollbar";
import router from "./router/Index";

import "@/assets/AdminStyles.scss";
import "@/assets/myStyles.scss";
import "@/assets/styles.scss";
import "@/assets/tailwind.css";
import { ability } from "@/plugins/ability";
import { abilitiesPlugin } from "@casl/vue";
import "vue3-perfect-scrollbar/style.css";
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
app.use(PerfectScrollbarPlugin);
app.use(ConfirmationService);
app.use(i18n);
app.use(pinia);

app.mount("#app");
