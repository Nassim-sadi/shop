<script setup>
const appName = ref(import.meta.env.VITE_APP_NAME);
import logo from "@/assets/shop/logo.avif";
import { ref } from "vue";

const navItems = ref([
    {
        label: "Store",
        icon: "ti ti-home",
        to: "/",
    },
    {
        label: "Categories",
        icon: "ti ti-list",
        to: "/categories",
    },
    {
        label: "About",
        icon: "ti ti-info-circle",
        to: "/about",
    },
    {
        label: "Contact",
        icon: "ti ti-phone",
        to: "/contact",
    },
]);
</script>
<template>
    <div class="navbar">
        <div class="navbar-logo-container">
            <router-link to="/" class="navbar-logo">
                <!-- <Image :src="logo" alt="logo" /> -->
                <span style="color: var(--light-clr)">{{ appName }}</span>
            </router-link>
        </div>

        <IconField>
            <InputIcon class="pi pi-search" />
            <InputText v-model="search" placeholder="Search" />
        </IconField>

        <div class="navbar-items">
            <template v-for="item in navItems">
                <router-link :to="item.to" class="navbar-item" v-ripple>
                    <i :class="item.icon"></i>
                    {{ item.label }}
                </router-link>
            </template>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.navbar {
    position: fixed;
    height: 6rem;
    z-index: 997;
    left: 0;
    top: 0;
    width: 100%;
    padding: 0 2rem;
    background-color: var(--black-clr);
    transition: left var(--layout-section-transition-duration);
    display: flex;
    align-items: center;
    justify-content: space-between;

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

    .navbar-items {
        display: flex;
        align-items: center;
        gap: 2rem;

        .navbar-item {
            color: var(--light-clr);
            font-size: 1.2rem;
            font-weight: 500;
            cursor: pointer;
        }
    }
}

@media (max-width: 991px) {
    .navbar {
        padding: 0 1rem;
    }
}
</style>
