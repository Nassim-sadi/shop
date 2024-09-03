import { authStore } from "@/store/AuthStore";
import axios from "axios";
const instance = axios.create({
    baseURL: import.meta.env.VITE_APP_BASE_URL,
    withCredentials: true,
    withXSRFToken: true,
});

instance.interceptors.request.use((config) => {
    const auth = authStore();
    config.headers.Accept = "application/json";
    config.headers["Content-Type"] = "application/json";
    if (!auth.token) return config;
    config.headers.Authorization = `Bearer ${auth.token}`;
    return config;
});

export default instance;
