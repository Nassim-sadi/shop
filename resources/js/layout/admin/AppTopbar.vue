<script setup>
import logo from "@/assets/images/logo.svg";
import { useLayout } from "@/layout/admin/composables/layout";
import router from "@/router/Index";
import { useConfirm } from "primevue/useconfirm";
import { computed, ref } from "vue";
import AppConfigurator from "./AppConfigurator.vue";
const { onMenuToggle, toggleDarkMode, isDarkTheme } = useLayout();
const confirm = useConfirm();

import placeholder from "@/assets/images/avatar/profile-placeholder.png";
import { $t } from "@/plugins/i18n";
import { authStore } from "@/store/AuthStore";
const auth = authStore();
const appName = ref(import.meta.env.VITE_APP_NAME);
const menu = ref();
const items = ref([
    {
        label: "Options",
        items: [
            {
                label: $t("settings.account"),
                icon: "ti ti-user",
                command: () => {
                    router.push({ name: "settings" });
                },
            },
            {
                label: $t("logout"),
                icon: "ti ti-logout-2",
                command: () => {
                    confirmLogout();
                },
            },
        ],
    },
]);

const user = computed(() => {
    return auth.user;
});

const toggleProfileMenu = (event) => {
    menu.value.toggle(event);
};

const confirmLogout = () => {
    confirm.require({
        header: $t("logout.confirm.header"),
        message: $t("logout.confirm.message"),
        icon: "pi pi-exclamation-triangle",
        rejectProps: {
            label: $t("cancel"),
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: $t("confirm.title"),
        },
        accept: () => {
            auth.logout();
        },
        reject: () => {},
    });
};
</script>

<template>
    <div class="layout-topbar rounded-bl-2xl rounded-br-2xl">
        <div class="layout-topbar-logo-container">
            <button
                class="layout-menu-button layout-topbar-action"
                @click="onMenuToggle"
            >
                <i class="pi pi-bars"></i>
            </button>

            <router-link to="/" class="layout-topbar-logo">
                <Image :src="logo" alt="logo" class="logo" />
                <span>{{ appName }}</span>
            </router-link>
        </div>

        <div class="layout-topbar-actions items-center">
            <div class="layout-config-menu">
                <a href="/">WebSite</a>

                <button
                    type="button"
                    class="layout-topbar-action"
                    @click="toggleDarkMode"
                >
                    <i
                        :class="[
                            'pi',
                            { 'pi-moon': isDarkTheme, 'pi-sun': !isDarkTheme },
                        ]"
                    ></i>
                </button>
                <div class="relative">
                    <button
                        v-styleclass="{
                            selector: '@next',
                            enterFromClass: 'hidden',
                            enterActiveClass: 'animate-scalein',
                            leaveToClass: 'hidden',
                            leaveActiveClass: 'animate-fadeout',
                            hideOnOutsideClick: true,
                        }"
                        type="button"
                        class="layout-topbar-action layout-topbar-action-highlight"
                    >
                        <i class="pi pi-palette"></i>
                    </button>
                    <AppConfigurator />
                </div>
            </div>

            <!-- <button
                class="layout-topbar-menu-button layout-topbar-action"
                v-styleclass="{
                    selector: '@next',
                    enterFromClass: 'hidden',
                    enterActiveClass: 'animate-scalein',
                    leaveToClass: 'hidden',
                    leaveActiveClass: 'animate-fadeout',
                    hideOnOutsideClick: true,
                }"
            >
                <i class="pi pi-ellipsis-v"></i>
            </button>

            <div class="layout-topbar-menu hidden lg:block">
                <div class="layout-topbar-menu-content items-center">
                    <button type="button" class="layout-topbar-action">
                        <i class="pi pi-calendar"></i>
                        <span>Calendar</span>
                    </button>
                    <button type="button" class="layout-topbar-action">
                        <i class="pi pi-inbox"></i>
                        <span>Messages</span>
                    </button>
                    <button
                        type="button"
                        @click="toggleProfileMenu"
                        aria-haspopup="true"
                        aria-controls="overlay_menu"
                    >
                        <Avatar
                            :image="
                                user && user.image ? user.image : placeholder
                            "
                            shape="circle"
                            size="large"
                        />
                    </button>
                </div>
            </div> -->
            <button
                type="button"
                @click="toggleProfileMenu"
                aria-haspopup="true"
                aria-controls="overlay_menu"
                style="display: flex"
            >
                <Avatar
                    :image="user && user.image ? user.image : placeholder"
                    shape="circle"
                    size="large"
                />
            </button>
        </div>
        <Menu
            :model="items"
            ref="menu"
            id="overlay_menu"
            class="w-full md:w-80 p-2"
            :popup="true"
        >
            <template #submenulabel="{ item }">
                <span class="text-primary font-bold">{{ item.label }}</span>
            </template>
            <template #item="{ item, props }">
                <a v-ripple class="flex items-center" v-bind="props.action">
                    <span :class="item.icon" class="text-xl" />
                    <span>{{ item.label }}</span>
                    <Badge
                        v-if="item.badge"
                        class="ml-auto"
                        :value="item.badge"
                    />
                    <span
                        v-if="item.shortcut"
                        class="ml-auto border border-surface rounded bg-emphasis text-muted-color text-xs p-1"
                        >{{ item.shortcut }}</span
                    >
                </a>
            </template>
            <template #start>
                <div
                    class="relative overflow-hidden w-full p-2 rounded-none transition-colors duration-200"
                >
                    <h2 class="font-bold text-xl mb-4">
                        {{ $t("user.profile") }}
                    </h2>
                    <div class="flex items-center gap-4">
                        <Avatar
                            :image="
                                user && user.image ? user.image : placeholder
                            "
                            shape="circle"
                            size="xlarge"
                        />

                        <div class="inline-flex flex-col items-start gap-1">
                            <span class="font-bold text-lg capitalize">{{
                                user.firstname + " " + user.lastname
                            }}</span>
                            <span class="text-muted-color text-base">{{
                                user.roles.name
                            }}</span>
                            <div
                                class="flex items-center gap-2 text-muted-color"
                            >
                                <span class="pi pi-envelope"></span>

                                <span class="text-sm"> {{ user.email }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Menu>
        <ConfirmDialog></ConfirmDialog>
    </div>
</template>

<style lang="scss" scoped></style>
