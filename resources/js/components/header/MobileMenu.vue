<script setup>
import { computed } from "vue";
import { useRouter, useRoute } from "vue-router";

// Props
const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    user: {
        type: Object,
        default: null,
    },
    navigationItems: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

// Emits
const emit = defineEmits(["update:visible", "search", "logout", "navigate"]);

// Composables
const router = useRouter();
const route = useRoute();

// Computed
const isVisible = computed({
    get: () => props.visible,
    set: (value) => emit("update:visible", value),
});

const userDisplayName = computed(() => {
    if (!props.user) return "";
    return props.user.name || props.user.email || "User";
});

const currentPath = computed(() => route.path);

// Methods
const handleClose = () => {
    emit("update:visible", false);
};

const handleNavigation = (destination) => {
    emit("navigate", destination);
    if (typeof destination === "string") {
        router.push(destination);
    } else if (destination && destination.name) {
        router.push(destination);
    }
    handleClose();
};

const handleLogout = () => {
    emit("logout");
    handleClose();
};
</script>

<template>
    <Drawer
        v-model:visible="isVisible"
        :header="$t('navigation', 'Navigation')"
        position="left"
        class="mobile-menu-drawer"
        @hide="handleClose"
    >
        <div class="mobile-menu-content">
            <!-- User Section -->
            <div v-if="user" class="mobile-user-section">
                <div class="mobile-user-info">
                    <i class="pi pi-user"></i>
                    <span>{{ userDisplayName }}</span>
                </div>

                <button
                    class="mobile-menu-item"
                    @click="handleNavigation({ name: 'dashboard' })"
                >
                    <i class="pi pi-home"></i>
                    {{ $t("dashboard") }}
                </button>

                <button
                    class="mobile-menu-item"
                    @click="handleNavigation({ name: 'profile' })"
                >
                    <i class="pi pi-user"></i>
                    {{ $t("profile") }}
                </button>

                <button
                    class="mobile-menu-item"
                    @click="handleNavigation({ name: 'orders' })"
                >
                    <i class="pi pi-shopping-cart"></i>
                    {{ $t("orders") }}
                </button>

                <button
                    class="mobile-menu-item logout-item"
                    @click="handleLogout"
                >
                    <i class="pi pi-sign-out"></i>
                    {{ $t("logout") }}
                </button>
            </div>

            <!-- Auth Section for Guests -->
            <div v-else class="mobile-auth-section">
                <Button
                    class="auth-item login"
                    @click="handleNavigation({ name: 'login' })"
                    outlined
                >
                    <i class="pi pi-sign-in"></i>
                    {{ $t("login") }}
                </Button>

                <Button
                    class="auth-item register"
                    @click="handleNavigation({ name: 'register' })"
                >
                    <i class="pi pi-user-plus"></i>
                    {{ $t("register") }}
                </Button>
            </div>

            <!-- Main Navigation -->
            <div class="mobile-nav-section">
                <template
                    v-for="item in navigationItems"
                    :key="item.to || item.label"
                >
                    <button
                        v-if="!item.isSpecial"
                        class="mobile-menu-item"
                        :class="{ active: currentPath === item.to }"
                        @click="handleNavigation(item.to)"
                    >
                        <i :class="`pi ${item.icon}`" v-if="item.icon"></i>
                        {{ item.label }}
                    </button>
                </template>
            </div>

            <!-- Categories Panel Menu -->
            <div class="mobile-categories-section">
                <h3>{{ $t("categories", "Categories") }}</h3>
                <div class="mobile-panel-menu-container">
                    <PanelMenu :model="categories" class="mobile-panel-menu">
                        <template #item="{ item }">
                            <div
                                class="mobile-category-item"
                                @click="
                                    item.to ? handleNavigation(item.to) : null
                                "
                                :class="{ 'has-link': item.to }"
                            >
                                <span class="category-label">{{
                                    item.label
                                }}</span>
                                <Badge
                                    v-if="item.badge"
                                    :value="item.badge"
                                    size="small"
                                    class="category-badge"
                                />
                            </div>
                        </template>
                    </PanelMenu>
                </div>
            </div>
        </div>
    </Drawer>
</template>

<style lang="scss" scoped>
.mobile-menu-drawer {
    :deep(.p-drawer-content) {
        padding: 0;
    }
}

.mobile-menu-content {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 1rem;
    gap: 1.5rem;
}

.mobile-search-section {
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

.mobile-user-section {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--surface-border);

    .mobile-user-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }
}

.mobile-auth-section {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--surface-border);

    .auth-item {
        justify-content: flex-start;
        gap: 0.5rem;
    }
}

.mobile-nav-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--surface-border);
}

.mobile-menu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: none;
    border: none;
    border-radius: var(--border-radius);
    color: var(--text-color);
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
    width: 100%;

    &:hover {
        background-color: var(--surface-hover);
        color: var(--primary-color);
    }

    &.active {
        background-color: var(--primary-color-text);
        color: var(--primary-color);
        font-weight: 600;
    }

    &.logout-item {
        color: var(--red-500);
        margin-top: 0.5rem;

        &:hover {
            background-color: var(--red-50);
            color: var(--red-600);
        }
    }

    i {
        font-size: 1.1rem;
        width: 1.2rem;
    }
}

.mobile-categories-section {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1rem;

    h3 {
        margin: 0;
        color: var(--text-color-secondary);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .mobile-panel-menu-container {
        flex: 1;
        overflow-y: auto;
    }

    .mobile-panel-menu {
        border: none;
        background: none;

        :deep(.p-panelmenu-panel) {
            border: none;
            margin-bottom: 0.5rem;
        }

        :deep(.p-panelmenu-header-link) {
            background: var(--surface-50);
            border: 1px solid var(--surface-border);
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
        }

        :deep(.p-panelmenu-content) {
            border: none;
            background: none;
            padding: 0.5rem 0 0 0;
        }
    }

    .mobile-category-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        transition: background-color 0.2s;

        &.has-link {
            cursor: pointer;

            &:hover {
                background-color: var(--surface-hover);
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

// Mobile responsive
@media (max-width: 768px) {
    .mobile-menu-drawer {
        :deep(.p-drawer) {
            width: 85vw !important;
            max-width: 320px;
        }
    }
}
</style>
