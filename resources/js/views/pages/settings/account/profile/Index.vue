<script setup>
import { $t } from "@/plugins/i18n";
import { defineProps } from "vue";
defineProps({
    user: Object,
});
</script>

<template>
    <div class="col-span-12 lg:col-span-6 card">
        <div class="font-semibold text-xl col-span-6">
            {{ $t("settings.account") }}
        </div>
        <div class="grid grid-cols-12 col-span-12">
            <Avatar
                :icon="user.avatar ? '' : 'pi pi-user'"
                class="col-span-12 mb-4"
                size="xlarge"
                shape="circle"
                :image="user.avatar ? user.avatar : ''"
                style="
                    background-color: var(--primary-color);
                    color: var(--surface-ground);
                "
            />
            <div class="col-span-12 flex">
                <p class="font-semibold">{{ $t("auth.email") }} :&#160;</p>
                <p>{{ user.email }}</p>
            </div>

            <div class="col-span-12 flex">
                <p class="font-semibold">{{ $t("firstname") }} :&#160;</p>
                <p>{{ user.firstname }}</p>
            </div>
            <div class="col-span-12 flex">
                <p class="font-semibold">{{ $t("lastname") }} :&#160;</p>
                <p>{{ user.lastname }}</p>
            </div>
            <div class="col-span-12 flex">
                <p class="font-semibold">{{ $t("role") }} :&#160;</p>
                <p>{{ user.role }}</p>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>
