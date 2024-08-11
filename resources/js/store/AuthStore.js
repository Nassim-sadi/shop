import axios from "@/plugins/axios";
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";
import router from "@/router/Index";
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
                        },
                    )
                    .then((response) => {
                        console.log(response);
                        this.user = null;
                        this.token = null;
                        router.push({ name: "login" });
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
                        console.log(response);
                        this.user = response.data;
                        resolve();
                    })
                    .catch((error) => {
                        this.user = null;
                        this.token = null;
                        emitter.emit("toast", {
                            summary: $t("getUserErrorSummary"),
                            message: $t("getUserErrorMessage"),
                            severity: "error",
                        });
                        throw error;
                    });
            });
        },

        async forgotPassword(email) {
            console.log(email);

            return new Promise((resolve, reject) => {
                axios
                    .post("api/admin/password/send-link", { email: email })
                    .then((response) => {
                        emitter.emit("toast", {
                            summary: $t("auth.otp_sent"),
                            message: $t("auth.otp_sent_message"),
                            severity: "success",
                        });
                        resolve(response);
                    })
                    .catch((error) => {
                        emitter.emit("toast", {
                            summary: $t("error"),
                            message: $t("error_message"),
                            severity: "error",
                        });
                        reject(error);
                    });
            });
        },

        async resetPassword(data) {
            console.log(data);

            return new Promise((resolve, reject) => {
                axios
                    .post("api/admin/password/reset", {
                        email: data.email,
                        token: data.token,
                        password: data.password,
                        password_confirmation: data.password_confirmation,
                    })
                    .then((response) => {
                        emitter.emit("reset-password", response);
                        emitter.emit("toast", {
                            summary: $t("auth.password_changed"),
                            message: $t("auth.password_changed_message"),
                            severity: "success",
                        });
                        resolve();
                    })
                    .catch((error) => {
                        emitter.emit("toast", {
                            summary: $t("error"),
                            message: $t("error_message"),
                            severity: "error",
                        });
                        console.log(error);
                        reject();
                    });
            });
        },
    },
});
