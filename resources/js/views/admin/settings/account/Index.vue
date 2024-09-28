<script setup>
import { authStore } from "@/store/AuthStore";
import { onMounted, ref } from "vue";
const auth = authStore();

import Profile from "./profile/Index.vue";
import Security from "./security/Index.vue";
let loading = ref(false);
const getUser = async () => {
    loading.value = true;
    await auth.getUser();
    loading.value = false;
};

const updateUser = (val) => {
    auth.user = { ...auth.user, ...val };
};

onMounted(async () => {
    await getUser();
});
</script>
<template>
    <div class="grid grid-cols-12 gap-8">
        <Profile
            :user="auth.user"
            :loading="loading"
            @update:user="updateUser"
        />
        <Security :user="auth.user" />
    </div>
</template>
<style lang="scss" scoped></style>
