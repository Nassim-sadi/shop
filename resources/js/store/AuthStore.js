import axios from "@/plugins/axios";
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
        login(user) {
            return new Promise((resolve, reject) => {
                axios
                    .post("api/admin/login", user)
                    .then((response) => {
                        this.user = response.data.user;
                        this.token = response.data.authorization.token;
                        resolve(response);
                    })
                    .catch((error) => {
                        console.log(error);
                        reject(error);
                    });
            });
        },

        logout() {
            return new Promise((resolve, reject) => {
                console.log("this token", this.token);
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
                        this.access_token = null;
                        resolve(response);
                    })
                    .catch((error) => {
                        reject(error);
                    });
            });
        },
    },
});
