<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import { $t } from "@/plugins/i18n";
import axios from "@/plugins/axios";
import ecommerceLogo from "@/assets/shop/logo-cropped.png";
import { authStore } from "@/store/AuthStore";
import MobileMenuDrawer from "./MobileMenu.vue";
import CategoriesSwiper from "./CategoriesSwiper.vue";
import { useMediaQuery } from "@vueuse/core";
import Select from "primevue/select";

const isMobile = useMediaQuery("(max-width: 768px)");
// Composables
const router = useRouter();
const route = useRoute();
const auth = authStore();

// Reactive state
const appName = ref(import.meta.env.VITE_APP_NAME);
const categories = ref([]);
const loading = ref(false);
const categoriesMenu = ref(null);
const search = ref("");
const isMobileMenuOpen = ref(false);
const selectedCategory = ref(-1);

// Computed properties
const menuCategories = computed(() => {
    if (!categories.value?.length) return [];
    return formatCategories(categories.value);
});

const swiperCategories = computed(() => {
    if (!categories.value?.length) return [];
    return formatCategoriesSwiper(categories.value);
});

// Alternative more concise version using reduce
const formatCategoriesSwiper = (categories) => {
    const flatten = (cats) =>
        cats.reduce((acc, category) => {
            const { children, ...categoryWithoutChildren } = category;
            acc.push(categoryWithoutChildren);
            if (children?.length) {
                acc.push(...flatten(children));
            }
            return acc;
        }, []);

    return flatten(categories);
};

const selectCategories = computed(() => {
    return [
        { id: -1, name: "All Categories", value: "all", slug: "all" },
        ...swiperCategories.value,
    ];
});

const navItems = computed(() => [
    {
        label: $t("store"),
        to: "/",
    },
    {
        label: $t("categories"),
        to: "/categories",
        isSpecial: true, // Special handling for categories dropdown
    },
    {
        label: $t("about"),
        to: "/about",
    },
    {
        label: $t("contact"),
        to: "/contact",
    },
]);

const userDisplayName = computed(() => {
    if (!auth.user) return "";
    return auth.user.firstname || auth.user.name || auth.user.email;
});

// Methods
const getCategories = async () => {
    if (loading.value) return; // Prevent duplicate requests
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .get("/api/categories")
            .then((response) => {
                categories.value = response.data || [];
                resolve(response.data);
            })
            .catch((error) => {
                console.error("Failed to fetch categories:", error);
                reject(error);
            })
            .finally(() => {
                loading.value = false;
            });
    });
};

const formatCategories = (categories) => {
    if (!Array.isArray(categories)) return [];

    const recursiveFormat = (category) => {
        if (!category || typeof category !== "object") return null;
        const hasChildren = category.children?.length > 0;
        return {
            id: category.id,
            label: category.name || "Unnamed Category",
            // Only add 'to' property for leaf categories (no children)
            to: !hasChildren ? `/categories/${category.slug}` : undefined,
            badge: hasChildren ? category.children.length : null,
            items: hasChildren
                ? category.children.map(recursiveFormat).filter(Boolean)
                : null,
        };
    };

    return categories.map(recursiveFormat).filter(Boolean);
};

const toggleCategoriesMenu = (event) => {
    if (categoriesMenu.value) {
        categoriesMenu.value.toggle(event);
    }
};

const handleSearch = () => {
    if (search.value.trim()) {
        router.push({
            name: "search",
            query: { q: search.value.trim() },
        });
    }
};

const handleSearchKeyup = (event) => {
    if (event.key === "Enter") {
        handleSearch();
    }
};

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};

const handleLogout = async () => {
    try {
        await auth.logout();
        router.push({ name: "home" });
    } catch (error) {
        console.error("Logout failed:", error);
    }
};

const handleMobileNavigation = (to) => {
    if (to) {
        router.push(to);
        closeMobileMenu();
    }
};

watch(route, () => {
    closeMobileMenu();
});

onMounted(() => {
    getCategories();
});
</script>

<template>
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <!-- Logo Section -->
        <div class="nav-container">
            <div class="navbar-left">
                <router-link
                    :to="{ name: 'home' }"
                    class="navbar-logo"
                    :aria-label="`${appName} - Go to homepage`"
                >
                    <Image
                        :src="ecommerceLogo"
                        :alt="`${appName} logo`"
                        class="logo"
                        loading="eager"
                    />
                    <!-- <span class="logo-text" v-if="appName">{{ appName }}</span> -->
                </router-link>
            </div>
            <!-- Search Section -->
            <div class="navbar-middle flex items-center gap-2 w-full">
                <InputGroup>
                    <!-- Search input -->
                    <InputText
                        v-model="search"
                        :placeholder="
                            $t('search_placeholder', 'Search products...')
                        "
                        @keyup="handleSearchKeyup"
                        :aria-label="$t('search_aria_label', 'Search products')"
                        class="pl-10 w-full"
                        :disabled="loading"
                        :loading="loading"
                    />
                    <!-- Dropdown -->
                    <Select
                        v-model="selectedCategory"
                        :options="selectCategories"
                        option-label="name"
                        option-value="id"
                        size="small"
                        class="search-select"
                        overlay-class="navbar-search-select-overlay"
                        v-if="!isMobile"
                    />
                    <!-- Search button -->
                    <!-- TODO: Add search loading  -->
                    <Button
                        @click="handleSearch"
                        :aria-label="$t('search_button_aria', 'Search')"
                        size="small"
                        icon="pi pi-search"
                        class="search-button"
                        severity="secondary"
                    />
                </InputGroup>
            </div>
            <!-- Desktop Navigation -->
            <div class="navbar-right desktop-only">
                <template v-if="!isMobile">
                    <!-- <template v-for="item in navItems" :key="item.to || item.label">
                        <button
                            v-if="
                                item.isSpecial &&
                                item.label.toLowerCase().includes('categories')
                            "
                            class="navbar-item navbar-button"
                            v-ripple
                            @click="toggleCategoriesMenu"
                            :aria-expanded="false"
                            aria-haspopup="true"
                            :aria-controls="`${item.label.toLowerCase()}-menu`"
                            :disabled="loading"
                        >
                            <i
                                :class="`pi ${item.icon}`"
                                v-if="item.icon"
                                aria-hidden="true"
                            ></i>
                            {{ item.label }}
                            <i class="pi pi-angle-down ml-1" aria-hidden="true"></i>
                        </button>
                        <router-link
                            v-else
                            :to="item.to"
                            class="navbar-item"
                            v-ripple
                            :class="{ active: route.path === item.to }"
                        >
                            <i
                                :class="`pi ${item.icon}`"
                                v-if="item.icon"
                                aria-hidden="true"
                            ></i>
                            {{ item.label }}
                        </router-link>
                    </template> -->
                    <!-- Authenticated User -->
                    <div v-if="auth.user" class="user-menu">
                        <Button
                            class="user-button"
                            :label="userDisplayName"
                            icon="pi pi-user"
                            outlined
                            @click="$refs.userMenu.toggle($event)"
                            aria-haspopup="true"
                            aria-controls="user-menu"
                        />
                        <Menu
                            ref="userMenu"
                            id="user-menu"
                            :model="[
                                {
                                    label: $t('dashboard'),
                                    icon: 'pi pi-home',
                                    command: () =>
                                        router.push({ name: 'dashboard' }),
                                },
                                {
                                    label: $t('profile'),
                                    icon: 'pi pi-user',
                                    command: () =>
                                        router.push({ name: 'profile' }),
                                },
                                {
                                    label: $t('orders'),
                                    icon: 'pi pi-shopping-cart',
                                    command: () =>
                                        router.push({ name: 'orders' }),
                                },
                                { separator: true },
                                {
                                    label: $t('logout'),
                                    icon: 'pi pi-sign-out',
                                    command: handleLogout,
                                },
                            ]"
                            :popup="true"
                        />
                    </div>
                    <!-- Guest User -->
                    <div v-else class="auth-buttons">
                        <router-link
                            :to="{ name: 'login' }"
                            class="navbar-item auth-link"
                            v-ripple
                        >
                            <i class="pi pi-sign-in" aria-hidden="true"></i>
                            {{ $t("login") }}
                        </router-link>
                        <router-link
                            :to="{ name: 'register' }"
                            class="navbar-item auth-link register-link"
                            v-ripple
                        >
                            <i class="pi pi-user-plus" aria-hidden="true"></i>
                            {{ $t("register") }}
                        </router-link>
                    </div>
                </template>
            </div>
            <!-- Mobile Menu Button -->
            <Button
                class="mobile-menu-toggle mobile-only"
                :icon="isMobileMenuOpen ? 'pi pi-times' : 'pi pi-bars'"
                @click="toggleMobileMenu"
                :aria-expanded="isMobileMenuOpen"
                aria-controls="mobile-menu"
                :aria-label="$t('toggle_mobile_menu', 'Toggle mobile menu')"
                text
                severity="secondary"
                v-if="isMobile"
            />
            <!-- Categories Dropdown Menu (Desktop) -->
            <TieredMenu
                ref="categoriesMenu"
                :model="menuCategories"
                :popup="true"
                :loading="loading"
                :id="`categories-menu`"
                class="categories-menu"
            >
                <template #item="{ item, props, hasSubmenu }">
                    <!-- Only make it a router-link if it has no children (leaf category) -->
                    <router-link
                        v-if="item.to && !hasSubmenu"
                        :to="item.to"
                        v-ripple
                        class="menu-item-link"
                        v-bind="props.action"
                    >
                        <span class="menu-item-label">{{ item.label }}</span>
                        <Badge
                            v-if="item.badge"
                            :value="item.badge"
                            class="ml-auto"
                            size="small"
                        />
                    </router-link>
                    <!-- For parent categories with children, use a regular button/div -->
                    <div
                        v-else
                        v-ripple
                        class="menu-item-link"
                        v-bind="props.action"
                        role="button"
                        :aria-expanded="hasSubmenu ? 'false' : undefined"
                        :aria-haspopup="hasSubmenu ? 'true' : undefined"
                        severity="secondary"
                    >
                        <span class="menu-item-label">{{ item.label }}</span>
                        <Badge
                            v-if="item.badge"
                            :value="item.badge"
                            class="ml-auto"
                            size="small"
                        />
                        <i
                            v-if="hasSubmenu"
                            class="pi pi-angle-right ml-auto"
                            aria-hidden="true"
                        ></i>
                    </div>
                </template>
            </TieredMenu>
        </div>

        <CategoriesSwiper :categories="swiperCategories" class="categories" />
    </nav>

    <!-- Mobile Menu Overlay -->
    <MobileMenuDrawer
        v-model:visible="isMobileMenuOpen"
        :user="auth.user"
        :navigation-items="navItems"
        :categories="menuCategories"
        @logout="handleLogout"
        @navigate="handleMobileNavigation"
    />
</template>

<style lang="scss" scoped>
.navbar {
    position: fixed;
    height: auto;
    z-index: 997;
    left: 0;
    top: 0;
    width: 100vw;
    background: var(--darkest-clr);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--surface-border);
    transition: all var(--layout-section-transition-duration);

    .nav-container {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 1rem;
        gap: 1rem;

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .navbar-logo {
            display: inline-flex;
            align-items: center;
            font-size: 1.5rem;
            border-radius: var(--border-radius);
            color: var(--primary-color-text);
            font-weight: 600;
            text-decoration: none;

            .logo {
                width: 2rem;
                height: auto;
                aspect-ratio: 1 / 1;
                object-fit: contain;
            }

            .logo-text {
                font-size: 1.25rem;
                font-weight: 700;
            }
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex: 1;
            height: 100%;

            .navbar-item,
            .navbar-button {
                color: var(--white-clr);
                font-size: 1rem;
                font-weight: 500;
                cursor: pointer;
                text-decoration: none;
                padding: 0.5rem 1rem;
                border-radius: var(--border-radius);
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                border: none;
                background: transparent;

                &:hover {
                    background: rgba(255, 255, 255, 0.1);
                    transform: translateY(-1px);
                }

                &.active {
                    background: rgba(255, 255, 255, 0.15);
                    font-weight: 600;
                }

                &:disabled {
                    opacity: 0.6;
                    cursor: not-allowed;
                }
            }
        }

        .navbar-middle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 10rem;
            width: 100%;
            --p-inputtext-focus-border-color: var(--accent-clr);
            .search-select {
                flex: 0 0 auto;
                width: auto;
                :deep(.p-select-label) {
                    font-weight: 600;
                }
            }

            .p-iconfield {
                width: 100%;
            }
        }

        .navbar-auth {
            display: flex;
            align-items: center;
            gap: 1rem;

            .user-menu {
                .user-button {
                    background: rgba(255, 255, 255, 0.1);
                    border-color: rgba(255, 255, 255, 0.2);
                    color: var(--primary-color-text);

                    &:hover {
                        background: rgba(255, 255, 255, 0.2);
                    }
                }
            }

            .auth-buttons {
                display: flex;
                gap: 0.5rem;

                .auth-link {
                    color: var(--primary-color-text);
                    font-size: 0.95rem;
                    font-weight: 500;
                    cursor: pointer;
                    text-decoration: none;
                    padding: 0.5rem 1rem;
                    border-radius: var(--border-radius);
                    transition: all 0.2s ease;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;

                    &:hover {
                        background: rgba(255, 255, 255, 0.1);
                    }

                    &.register-link {
                        background: var(--primary-color-text);
                        color: var(--primary-color);
                        font-weight: 600;

                        &:hover {
                            background: rgba(255, 255, 255, 0.9);
                            transform: translateY(-1px);
                        }
                    }
                }
            }
        }

        .mobile-menu-toggle {
            color: var(--text-clr);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.2s ease;

            &:hover {
                background: rgba(255, 255, 255, 0.2);
            }
        }

        .categories {
            height: 2rem !important;
        }
    }
}
</style>
