<script setup>
const appName = ref(import.meta.env.VITE_APP_NAME);
import placeholder from "@/assets/images/avatar/profile-placeholder.png";
import logo from "@/assets/images/logo.svg";
import router from "@/router/Index";
import { ref } from "vue";
</script>
<template>
    <div class="navbar rounded-bl-2xl rounded-br-2xl">
        <div class="navbar-logo-container">
            <router-link to="/" class="navbar-logo">
                <Image :src="logo" alt="logo" class="logo" />
                <span>{{ appName }}</span>
            </router-link>
        </div>

        <div class="layout-topbar-actions items-center">
            <div class="layout-config-menu">
                <div class="relative"></div>
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
        </div>
        <!-- <Menu
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
        </Menu> -->
    </div>
</template>

<style lang="scss" scoped>
.navbar {
    position: fixed;
    height: 4rem;
    z-index: 997;
    left: 0;
    top: 0;
    width: 100%;
    padding: 0 2rem;
    background-color: var(--surface-card);
    transition: left var(--layout-section-transition-duration);
    display: flex;
    align-items: center;

    .navbar-logo-container {
        width: 20rem;
        display: flex;
        align-items: center;
    }

    .navbar-logo {
        display: inline-flex;
        align-items: center;
        font-size: 1.5rem;
        border-radius: var(--content-border-radius);
        color: var(--text-color);
        font-weight: 500;
        gap: 0.5rem;

        svg {
            width: 3rem;
        }
    }
}

@media (max-width: 991px) {
    .navbar {
        padding: 0 2rem;
    }
}
</style>
