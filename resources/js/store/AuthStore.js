import { defineStore } from "pinia";
import axios from "@/plugins/axios";
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
                    .post("/login", user)
                    .then((response) => {
                        console.log(response);
                        resolve(response);
                    })
                    .catch((error) => {
                        console.log(error);
                        reject(error);
                    });
            });
        },
    },
});
