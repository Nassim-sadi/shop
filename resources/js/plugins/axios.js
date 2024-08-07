import router from "@/router/Index.js";
import axios from "axios";

const instance = axios.create({
    baseURL: import.meta.env.VITE_APP_BASE_URL,
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        Accept: "application/json",

    },
});

instance.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            router.push({ name: "login" });
            return Promise.resolve({ error: 'Unauthorized', redirect: true });
        }
        return Promise.reject(error);
    }
);


export default instance;
