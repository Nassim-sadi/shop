import Aura from "@primevue/themes/aura";
import PrimeVue from "primevue/config";
import ConfirmationService from "primevue/confirmationservice";
import ToastService from "primevue/toastservice";
import { createApp } from "vue";
import App from "./App.vue";
// custom imports
import i18n from "@/i18n";
import mitt from "mitt";
import { createPinia } from "pinia";
import piniaPersist from "pinia-plugin-persist";
import router from "./router/Index";

import "@/assets/styles.scss";
import "@/assets/tailwind.css";

const app = createApp(App);
const pinia = createPinia();
pinia.use(piniaPersist);

const emitter = mitt(); // Initialize mitt

app.provide("emitter", emitter);
app.use(router);
app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: ".app-dark",
        },
    },
});
app.use(ToastService);
app.use(ConfirmationService);
app.use(i18n);
app.use(pinia);

app.mount("#app");
