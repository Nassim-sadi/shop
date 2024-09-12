import axios from "@/plugins/axios";
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";
import router from "@/router/Index";
import { defineStore } from "pinia";

export const authStore = defineStore("authStore", {
    state: () => ({
        user: null,
        token: null,
        tokenExpiration: null,
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
        async login(user, rememberMe) {
            return new Promise((resolve, reject) => {
                axios
                    .post("api/admin/login", user, { remember_me: rememberMe })
                    .then((response) => {
                        this.user = response.data.user;
                        this.token = response.data.authorization.token;
                        this.tokenExpiration =
                            response.data.authorization.expires_at;
                        resolve(response);
                    })
                    .catch((error) => {
                        reject(error);
                    });
            });
        },

        async logout() {
            return new Promise((resolve, reject) => {
                axios
                    .post("api/admin/logout")
                    .then((response) => {
                        console.log(response);
                        this.user = null;
                        this.token = null;
                        this.tokenExpiration = null;
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
                    .get("api/admin/user")
                    .then((response) => {
                        this.user = response.data;
                        resolve();
                    })
                    .catch((error) => {
                        this.user = null;
                        this.token = null;
                        router.push({ name: "login" });
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
                        // emitter.emit("toast", {
                        //     summary: $t("error"),
                        //     message: $t("error_message"),
                        //     severity: "error",
                        // });
                        reject(error);
                    });
            });
        },

        async resetPassword(data) {
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
                        resolve(response);
                    })
                    .catch((error) => {
                        emitter.emit("toast", {
                            summary: $t("error"),
                            message: $t("error_message"),
                            severity: "error",
                        });
                        console.log(error);
                        reject(error);
                    });
            });
        },

        async refresh() {
            return new Promise((resolve, reject) => {
                axios
                    .post("api/admin/refresh")
                    .then((response) => {
                        this.user = response.data.user;
                        this.token = response.data.authorization.token;
                        resolve(response);
                    })
                    .catch((error) => {
                        this.user = null;
                        this.token = null;
                        router.push({ name: "login" });
                        reject(error);
                    });
            });
        },
    },
});
