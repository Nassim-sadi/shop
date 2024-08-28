<script setup>
import { $t } from "@/plugins/i18n";
defineProps({
    user: Object,
});
</script>

<template>
    <div class="col-span-12 grid grid-cols-12 xl:col-span-6 card gap-8">
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
                    {{ $t("auth.email") }} :&#160;
                </p>
                <p>{{ user.email }}</p>
                <Button text label="Change Email"></Button>
            </div>
        </div>

        <div class="col-span-12">
            <div class="flex items-center">
                <p class="font-semibold text-surface-900 dark:text-surface-0">
                    <i class="pi pi-envelope"></i>
                    {{ $t("auth.password") }} :&#160;
                </p>
                <p>*******</p>
                <Button text label="Change Password"></Button>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>
