<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import { $t } from "@/plugins/i18n";
import axios from "@/plugins/axios";
import ecommerceLogo from "@/assets/shop/logo-cropped.png";
import { authStore } from "@/store/AuthStore";
import MobileMenuDrawer from "./MobileMenu.vue";

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
const selectedCategory = ref(null);

// Computed properties
const formattedCategories = computed(() => {
    if (!categories.value?.length) return [];
    return formatCategories(categories.value);
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
    try {
        const response = await axios.get("/api/categories");
        categories.value = response.data || [];
    } catch (error) {
        console.error("Failed to fetch categories:", error);
        categories.value = [];
    } finally {
        loading.value = false;
    }
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

// Add this method to your existing methods in the parent component
const handleMobileSearch = (query) => {
    if (query.trim()) {
        router.push({
            name: "search",
            query: { q: query.trim() },
        });
    }
};

// Watchers
watch(route, () => {
    closeMobileMenu();
});

// Lifecycle
onMounted(() => {
    getCategories();
});
</script>

<template>
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <!-- Logo Section -->
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
                <!-- Dropdown -->
                <Select
                    v-model="selectedCategory"
                    :options="categories"
                    optionLabel="name"
                    optionValue="id"
                    :placeholder="$t('select_category', 'All')"
                    size="small"
                    class="select"
                />

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

                <!-- Search button -->

                <Button
                    @click="handleSearch"
                    :aria-label="$t('search_button_aria', 'Search')"
                    size="small"
                    icon="pi pi-search"
                    class="search-button"
                />
            </InputGroup>
        </div>

        <!-- Desktop Navigation -->
        <div class="navbar-right desktop-only">
            <template v-if="!isMobile">
                <template v-for="item in navItems" :key="item.to || item.label">
                    <!-- Categories Dropdown Button -->
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

                    <!-- Regular Navigation Links -->
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
                </template>

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
                                command: () => router.push({ name: 'profile' }),
                            },
                            {
                                label: $t('orders'),
                                icon: 'pi pi-shopping-cart',
                                command: () => router.push({ name: 'orders' }),
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

                    <!-- <router-link
                    :to="{ name: 'register' }"
                    class="navbar-item auth-link register-link"
                    v-ripple
                >
                    <i class="pi pi-user-plus" aria-hidden="true"></i>
                    {{ $t("register") }}
                </router-link> -->
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
            :model="formattedCategories"
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
    </nav>

    <!-- Mobile Menu Overlay -->
    <MobileMenuDrawer
        v-model:visible="isMobileMenuOpen"
        :user="auth.user"
        :navigation-items="navItems"
        :categories="formattedCategories"
        @logout="handleLogout"
        @navigate="handleMobileNavigation"
    />
</template>

<style lang="scss" scoped>
.navbar {
    position: fixed;
    height: 5rem;
    z-index: 997;
    left: 0;
    top: 0;
    width: 100%;
    padding: 0 2rem;
    background: linear-gradient(
        135deg,
        var(--primary-color),
        var(--primary-color-dark)
    );
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--surface-border);
    transition: all var(--layout-section-transition-duration);
    display: flex;
    align-items: center;
    gap: 2rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);

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
            width: 3rem;
            height: 3rem;
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

        .navbar-item,
        .navbar-button {
            color: var(--primary-color-text);
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
        min-width: 20rem;
        width: 100%;

        .select {
            flex: 0 0 auto;
            width: auto;
            :deep .p-select-label {
                font-size: smaller;
            }
        }

        .p-iconfield {
            width: 100%;
        }

        .search-input {
            width: 100%;
        }

        .search-button {
            flex-shrink: 0;
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
        color: var(--primary-color-text);
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.2s ease;

        &:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    }
}

.categories-menu {
    .menu-item-link {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 0.75rem;
        text-decoration: none;
        color: var(--text-color);
        transition: background-color 0.2s ease;

        &:hover {
            background: var(--surface-hover);
        }

        .menu-item-label {
            flex: 1;
        }
    }
}

// Mobile Menu Styles
.mobile-menu-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background: rgba(0, 0, 0, 0.6);
    z-index: 999;
    backdrop-filter: blur(8px);

    .mobile-menu {
        position: absolute;
        top: 6rem;
        right: 0;
        width: 22rem;
        max-width: 85vw;
        background: var(--surface-card);
        border-radius: var(--border-radius) 0 0 var(--border-radius);
        box-shadow: 0 12px 48px rgba(0, 0, 0, 0.25);
        max-height: calc(100vh - 6rem);
        overflow-y: auto;
        border: 1px solid var(--surface-border);

        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem;
            background: linear-gradient(
                135deg,
                var(--primary-color),
                var(--primary-color-dark)
            );
            color: var(--primary-color-text);
            position: sticky;
            top: 0;
            z-index: 1;

            h2 {
                margin: 0;
                font-size: 1.25rem;
                font-weight: 700;
            }

            .close-button {
                color: var(--primary-color-text);
            }
        }

        .mobile-menu-content {
            padding: 0.75rem 0;

            .mobile-search-section {
                padding: 0.75rem 1rem;
                background: var(--surface-ground);
                margin-bottom: 0.5rem;
                display: flex;
                gap: 0.5rem;
                align-items: center;

                .mobile-search-input {
                    flex: 1;
                }

                .mobile-search-button {
                    flex-shrink: 0;
                }
            }

            .mobile-auth-section {
                padding: 0.75rem;
                background: var(--surface-ground);
                margin-bottom: 0.5rem;
                display: flex;
                gap: 0.5rem;

                .auth-item {
                    flex: 1;
                    justify-content: center;
                    font-weight: 600;
                    border-radius: var(--border-radius);

                    &.login {
                        background: var(--surface-hover);
                    }

                    &.register {
                        background: var(--primary-color);
                        color: var(--primary-color-text);
                    }
                }
            }

            .mobile-user-section {
                padding: 0.75rem;
                background: var(--surface-ground);
                margin-bottom: 0.5rem;
                border-radius: var(--border-radius);

                .mobile-user-info {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    padding: 0.75rem;
                    font-weight: 600;
                    color: var(--primary-color);
                    border-bottom: 1px solid var(--surface-border);
                    margin-bottom: 0.5rem;

                    i {
                        font-size: 1.5rem;
                    }
                }

                .logout-item {
                    color: var(--red-500);

                    &:hover {
                        background: var(--red-50);
                        color: var(--red-600);
                    }
                }
            }

            .mobile-nav-section {
                margin-bottom: 0.5rem;
            }

            .mobile-menu-item {
                display: flex;
                align-items: center;
                gap: 0.875rem;
                padding: 1rem 1.25rem;
                text-decoration: none;
                color: var(--text-color);
                transition: all 0.2s ease;
                font-weight: 500;
                width: 100%;
                text-align: left;
                border: none;
                background: none;
                cursor: pointer;

                &:hover {
                    background: var(--surface-hover);
                    padding-left: 1.5rem;
                }

                &.active {
                    background: var(--primary-color-light);
                    color: var(--primary-color);
                    font-weight: 600;
                    border-left: 3px solid var(--primary-color);
                }

                i {
                    width: 1.25rem;
                    text-align: center;
                }
            }

            .mobile-categories-section {
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 2px solid var(--surface-border);

                h3 {
                    padding: 0.75rem 1.25rem 0.5rem;
                    margin: 0 0 0.5rem 0;
                    font-size: 0.85rem;
                    font-weight: 700;
                    color: var(--text-color-secondary);
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }

                .mobile-panel-menu-container {
                    padding: 0 1rem;

                    .mobile-panel-menu {
                        border: none;
                        box-shadow: none;

                        :deep(.p-panelmenu-panel) {
                            border: none;
                            margin-bottom: 0.25rem;
                        }

                        :deep(.p-panelmenu-header) {
                            background: var(--surface-ground);
                            border: 1px solid var(--surface-border);
                            border-radius: var(--border-radius);

                            .p-panelmenu-header-content {
                                padding: 0.75rem 1rem;
                                font-weight: 500;
                            }
                        }

                        :deep(.p-panelmenu-content) {
                            border: 1px solid var(--surface-border);
                            border-top: none;
                            border-radius: 0 0 var(--border-radius)
                                var(--border-radius);
                            background: var(--surface-card);
                        }

                        .mobile-category-item {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            padding: 0.75rem 1rem;
                            cursor: pointer;
                            transition: background-color 0.2s ease;

                            &.has-link {
                                color: var(--primary-color);
                                font-weight: 500;

                                &:hover {
                                    background: var(--primary-color-light);
                                }
                            }

                            .category-label {
                                flex: 1;
                            }

                            .category-badge {
                                margin-left: 0.5rem;
                            }
                        }
                    }
                }
            }
        }
    }
}

@media (max-width: 768px) {
}
</style>
