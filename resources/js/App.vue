<template>
    <router-view v-slot="{ Component, route }">
        <Toast position="top-right"> </Toast>
        <div :key="route.name">
            <Component :is="Component" />
        </div>
    </router-view>
</template>

<script setup>
import axios from "@/plugins/axios";
import { authStore } from "@/store/AuthStore";
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";
const toast = useToast();
const auth = authStore();

import emitter from "@/plugins/emitter";
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
const router = useRouter();
const route = useRoute();

const showToast = () => {
    emitter.on("toast", ({ summary, message, severity, life }) => {
        toast.add({
            severity: severity,
            summary: summary,
            detail: message,
            life: life || 3000,
        });
    });
};

const excludedRoutes = ["login", "forgot-password", "reset-password"];

const checkAuth = () => {
    if (excludedRoutes.includes(route.name)) {
        return;
    }
    auth.getUser();
};

onMounted(async () => {
    await router.isReady();
    await axios.get("/sanctum/csrf-cookie");
    checkAuth();
    showToast();
});
</script>

<style lang="scss" scoped></style>
