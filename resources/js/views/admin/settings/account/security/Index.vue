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

const edit = () => {
    sidebar.value = true;
};

const popover = ref();
const togglePopover = (event) => {
    popover.value.toggle(event);
};
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

    <div class="grid grid-cols-12 col-span-12 lg:col-span-6 card mb-0">
        <div
            class="font-semibold text-surface-900 dark:text-surface-0 text-xl col-span-12 flex justify-between items-baseline mb-4"
        >
            <h3>
                {{ $t("settings.security") }}
            </h3>

            <div class="hidden lg:block">
                <Button
                    text
                    class="bg-blue-100 dark:bg-blue-400/10 rounded-border"
                    :label="$t('user.edit_password')"
                    icon="pi pi-pencil"
                    @click="edit"
                />
            </div>

            <div class="block lg:hidden">
                <Button text icon="pi pi-ellipsis-v" @click="togglePopover" />
            </div>

            <Popover ref="popover">
                <Button
                    text
                    icon="pi pi-pencil"
                    class="bg-blue-100 dark:bg-blue-400/10 rounded-border"
                    :label="$t('user.edit_password')"
                    @click="edit"
                />
            </Popover>
        </div>

        <div class="col-span-12">
            <div class="flex items-center">
                <p class="font-semibold">
                    <i class="pi pi-lock"></i>
                    {{ $t("auth.password") }} :&#160;
                </p>
                <p>*******</p>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>
