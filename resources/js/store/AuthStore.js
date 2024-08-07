import { $t } from "@/i18n";
import axios from "@/plugins/axios";
import emitter from "@/plugins/emitter";
import { defineStore } from "pinia";

export const authStore = defineStore("authStore", {
    state: () => ({
        user: null,
        token: null,
    }),
    persist: {
        enabled: true,
        strategies: [
            {
                storage: localStorage,
            },
        ],
    },
    actions: {
        async login(user) {
            return new Promise((resolve, reject) => {
                axios
                    .post("api/admin/login", user)
                    .then((response) => {
                        this.user = response.data.user;
                        this.token = response.data.authorization.token;
                        resolve();
                    })
                    .catch((error) => {
                        console.log(error);
                        // emitter.emit("toast", ({ summary: $t("getUserErrorSummary"), message: $t("getUserErrorMessage"), severity: "error" }));
                        reject();
                    });
            });
        },

        async logout() {
            return new Promise((resolve, reject) => {
                axios
                    .post(
                        "api/admin/logout",
                        {},
                        {
                            headers: {
                                Authorization: "Bearer " + this.token,
                                // "X-Authorization": import.meta.env.VITE_API_KEY,
                            },
                        }
                    )
                    .then((response) => {
                        console.log(response);
                        this.user = null;
                        this.token = null;
                        resolve(response);
                    })
                    .catch((error) => {
                        reject(error);
                    });
            });
        },

        async getUser() {
            return new Promise((resolve, reject) => {
                axios
                    .get("api/admin/user", {
                        headers: {
                            Authorization: "Bearer " + this.token,
                            // "X-Authorization": import.meta.env.VITE_API_KEY,
                        },

                    })
                    .then((response) => {
                        this.user = response.data;
                        resolve();
                    })
                    .catch((error) => {
                        this.user = null;
                        this.token = null;
                        emitter.emit("toast", ({ summary: $t("getUserErrorSummary"), message: $t("getUserErrorMessage"), severity: "error" }));
                        throw error;
                    });
            });
        },
    },
});
