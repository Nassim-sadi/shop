<script setup>
const appName = ref(import.meta.env.VITE_APP_NAME);
import logo from "@/assets/shop/logo.avif";
import ecommerceLogo from "@/assets/shop/logo.png";
import { onMounted, ref } from "vue";
import axios from "@/plugins/axios";
const categories = ref([]);
const formattedCategories = ref([]);
const loading = ref(false);
const categoriesMenu = ref(false);
import { authStore } from "@/store/AuthStore";
const auth = authStore();
const getCategories = async () => {
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .get("/api/categories")
            .then((response) => {
                console.log(response.data);
                categories.value = response.data;
                resolve(response.data);
            })
            .catch((error) => {
                reject(error);
            })
            .finally(() => {
                loading.value = false;
            });
    });
};

function formatCategories(categories) {
    function recursiveFormat(category) {
        const formattedCategory = {
            id: category.id,
            label: category.name,
            to: `/categories/${category.slug}`,
            badge: category.children ? category.children.length : null,
            items:
                category.children.length > 0
                    ? category.children.map(recursiveFormat)
                    : null,
        };
        return formattedCategory;
    }
    return categories.map(recursiveFormat);
}
const navItems = ref([
    {
        label: "Store",
        to: "/",
    },
    {
        label: "Categories",
        to: "/categories",
    },
    {
        label: "About",
        to: "/about",
    },
    {
        label: "Contact",
        to: "/contact",
    },
]);

const toggleMenu = (event) => {
    categoriesMenu.value.toggle(event);
};

onMounted(async () => {
    await getCategories();
    formattedCategories.value = formatCategories(categories.value);
});
</script>
<template>
    <div class="navbar">
        <div class="navbar-logo-container">
            <router-link :to="{ name: 'home' }" class="navbar-logo">
                <Image :src="logo" alt="logo" class="logo" />
            </router-link>
        </div>

        <div class="navbar-items">
            <template v-for="item in navItems">
                <button
                    class="navbar-item"
                    v-ripple
                    v-if="item.label === 'Categories'"
                    @click="toggleMenu"
                    aria-haspopup="true"
                    aria-controls="overlay_tmenu"
                >
                    {{ item.label }}
                </button>

                <router-link v-else :to="item.to" class="navbar-item" v-ripple>
                    {{ item.label }}
                </router-link>
            </template>
        </div>

        <IconField>
            <InputIcon class="pi pi-search" />
            <InputText v-model="search" placeholder="Search" />
        </IconField>

        <div class="navbar-auth text-white">
            <router-link
                v-if="auth.user"
                :to="{ name: 'dashboard' }"
                class="navbar-item"
                v-ripple
            >
                {{ auth.user.firstname }}
                Admin
            </router-link>
            <router-link
                v-else
                :to="{ name: 'login' }"
                class="navbar-item"
                v-ripple
            >
                {{ $t("login") }}
            </router-link>
        </div>
    </div>

    <TieredMenu
        :model="formattedCategories"
        key="id"
        class="w-full md:w-80 p-2"
        :popup="true"
        :loading="loading"
        ref="categoriesMenu"
        id="overlay_tmenu"
    >
        <template #item="{ item, props, hasSubmenu }">
            <a v-ripple class="flex" v-bind="props.action">
                <span class="ml-2">{{ item.label }}</span>
                <!-- <Badge v-if="item.badge" class="ml-auto" :value="item.badge" /> -->
                <span v-if="hasSubmenu" class="ml-auto">
                    <i class="pi pi-fw pi-angle-right"></i>
                </span>
                <span
                    v-if="item.shortcut"
                    class="ml-auto border border-surface rounded bg-emphasis text-muted-color text-xs p-1"
                    >{{ item.shortcut }}</span
                >
            </a>
        </template>
    </TieredMenu>
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
    gap: 2rem;

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

        .logo {
            width: 16rem;
        }
    }

    .navbar-items {
        display: flex;
        align-items: center;
        gap: 1rem;

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
