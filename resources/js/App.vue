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
import emitter from "@/plugins/emitter";
import { authStore } from "@/store/AuthStore";
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";
const toast = useToast();

import { onMounted } from "vue";
import { useRouter } from "vue-router";
const auth = authStore();
const router = useRouter();

onMounted(async () => {
    await axios.get("/sanctum/csrf-cookie");
    checkAuth();
    showToast();
});

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
const checkAuth = async () => {
    if (router.currentRoute.value.name == "login") return;
    await auth.getUser();
};
</script>

<style lang="scss" scoped></style>
