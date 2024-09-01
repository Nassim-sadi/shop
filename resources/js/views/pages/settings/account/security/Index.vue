<script setup>
import axios from "@/plugins/axios";
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";
import { ref } from "vue";
import Edit from "./sidebars/Edit.vue";

defineProps({
    user: Object,
});

const loading = ref(false);

const sidebar = ref(false);
const changePassword = (val) => {
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/change-password", {
                old_password: val.oldPassword,
                password: val.newPassword,
                password_confirmation: val.confirmPassword,
            })
            .then((response) => {
                loading.value = false;
                sidebar.value = false;
                emitter.emit("toast", {
                    summary: $t("update.success"),
                    message: $t("update.success_message"),
                    severity: "success",
                });
                resolve(response);
            })
            .catch((error) => {
                loading.value = false;
                console.log(error);
                reject(error);
            });
    });
};
</script>

<template>
    <Edit
        @submit="changePassword"
        v-model:isOpen="sidebar"
        :loading="loading"
    />

    <div class="grid grid-cols-12 col-span-12 md:col-span-6 card gap-8">
        <div
            class="font-semibold text-surface-900 dark:text-surface-0 text-xl col-span-12 flex justify-between"
        >
            <h3>
                {{ $t("settings.security") }}
            </h3>
        </div>

        <div class="col-span-12">
            <div class="flex items-center">
                <p class="font-semibold text-surface-900 dark:text-surface-0">
                    <i class="pi pi-envelope"></i>
                    {{ $t("auth.password") }} :&#160;
                </p>
                <p>*******</p>
                <Button
                    text
                    label="Change Password"
                    @click="sidebar = true"
                ></Button>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>
