import router from "@/router/Index";
import { authStore } from "@/store/AuthStore";
import axios from "axios";
const isTokenExpired = () => {
    const expiresAt = authStore().tokenExpiration;
    if (!expiresAt) return false;
    const expirationTime = new Date(expiresAt).getTime();
    const currentTime = new Date().getTime();

    return currentTime > expirationTime;
};

const instance = axios.create({
    baseURL: import.meta.env.VITE_APP_BASE_URL,
    withCredentials: true,
    withXSRFToken: true,
});

instance.interceptors.request.use((config) => {
    const auth = authStore();
    if (isTokenExpired()) {
        auth.token = null;
        auth.tokenExpiration = null;
        auth.user = null;
        router.push({ name: "login" });
    }

    config.headers.Accept = "application/json";
    if (!auth.token) return config;
    config.headers.Authorization = `Bearer ${auth.token}`;
    return config;
});

instance.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            // Handle token refresh or logout
            authStore().token = null;
            authStore().tokenExpiration = null;
            authStore().user = null;
            router.push({ name: "login" });
        }
        return Promise.reject(error);
    },
);
export default instance;
