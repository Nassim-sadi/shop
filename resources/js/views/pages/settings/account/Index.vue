<script setup>
import axios from "@/plugins/axios";
import { onMounted, ref } from "vue";
const user = ref({});

import Profile from "./profile/Index.vue";
import Security from "./security/Index.vue";

const getUser = async () => {
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/user")
            .then((response) => {
                user.value = response.data;
                resolve(response);
            })
            .catch((error) => {
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
        <Profile :user="user" />
        <Security :user="user" />
    </div>
</template>
<style lang="scss" scoped></style>
