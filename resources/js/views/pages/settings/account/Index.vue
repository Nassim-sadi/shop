<script setup>
import { $t } from "@/plugins/i18n";
import { authStore } from "@/store/AuthStore";
import { onMounted, ref } from "vue";
const auth = authStore();
const user = ref({});

import Profile from "./profile/Index.vue";
import Security from "./security/Index.vue";
const getUser = () => {
    user.value = auth.user;
};

onMounted(() => {
    getUser();
});
</script>
<template>
    <div class="grid grid-cols-12 gap-8">
        <Profile :user="user" />
        <Security />
    </div>
</template>
<style lang="scss" scoped></style>
