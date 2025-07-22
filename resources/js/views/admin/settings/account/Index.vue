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
    <div class="card flex flex-col gap-4">
        <Profile
            :user="auth.user"
            :loading="loading"
            @update:user="updateUser"
            v-if="auth.user"
        />
        <Security :user="auth.user" v-if="auth.user" />
    </div>
</template>
<style lang="scss" scoped></style>
