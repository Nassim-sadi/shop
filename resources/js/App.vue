<template>
    <router-view v-slot="{ Component, route }">
        <div :key="route.name">
            <Component :is="Component" />
        </div>
    </router-view>
</template>

<script setup>
import axios from "@/plugins/axios";
import { authStore } from "@/store/AuthStore";
import { onMounted } from "vue";
const auth = authStore();

import { useRouter } from "vue-router";
const router = useRouter();

onMounted(async () => {
    await axios.get("/sanctum/csrf-cookie");
    checkAuth();
});

const checkAuth = () => {
    if (router.currentRoute.value.name == "login") return;
    auth.getUser().then((response) => {
        if (response.status == 401) {
            router.push({ name: "login" });
        }
    });
};
</script>

<style lang="scss" scoped></style>
