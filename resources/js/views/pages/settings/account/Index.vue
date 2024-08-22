<script setup>
import axios from "@/plugins/axios";
import { onMounted, ref } from "vue";
const user = ref({});

import Profile from "./profile/Index.vue";
import Security from "./security/Index.vue";
let loading = ref(false);
const getUser = async () => {
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/user")
            .then((response) => {
                user.value = response.data;
                loading.value = false;
                resolve(response);
            })
            .catch((error) => {
                loading.value = false;
                reject(error);
            });
    });
};

onMounted(async () => {
    await getUser();
});
</script>
<template>
    <div class="grid grid-cols-12 gap-8">
        <Profile :user="user" :loading="loading" />
        <Security :user="user" :loading="loading" />
    </div>
</template>
<style lang="scss" scoped></style>
