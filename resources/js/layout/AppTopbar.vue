<script setup>
import logoDark from "@/assets/images/logo-dark.svg";
import logoLight from "@/assets/images/logo-light.svg";
import { useLayout } from "@/layout/composables/layout";
import { authStore } from "@/store/AuthStore";
import { computed, inject, onBeforeUnmount, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";

const { t } = useI18n();
const { layoutConfig, onMenuToggle } = useLayout();

const router = useRouter();
const auth = authStore();

const emitter = inject("emitter");
const outsideClickListener = ref(null);
const topbarMenuActive = ref(false);
const menuVisible = ref(false);
const menuRef = ref(null);

const items = ref([
    {
        label: t("profile"),
        icon: "pi pi-user",
        command: () => {
            onProfileButton();
        },
    },
    {
        label: t("logout"),
        icon: "pi pi-sign-out",
        command: () => {
            logout();
        },
    },
]);

onMounted(() => {
    bindOutsideClickListener();
});

onBeforeUnmount(() => {
    unbindOutsideClickListener();
});

const logoUrl = computed(() => {
    if (layoutConfig.darkTheme.value) {
        return logoLight;
    } else {
        return logoDark;
    }
});

const onTopBarMenuButton = () => {
    topbarMenuActive.value = !topbarMenuActive.value;
    router.push("/");
};
const onSettingsClick = () => {
    topbarMenuActive.value = false;
    emitter.emit("config-button-click");
};

const topbarMenuClasses = computed(() => {
    return {
        "layout-topbar-menu-mobile-active": topbarMenuActive.value,
    };
});

const logout = () => {
    auth.logout().then(() => {
        router.push({ name: "login" });
    });
};

const onProfileButton = (event) => {
    menuVisible.value = !menuVisible.value;
    if (menuVisible.value) {
        menuRef.value.show(event);
    } else {
        menuRef.value.hide();
    }
};

const onMenuHide = () => {
    menuVisible.value = false;
};

const bindOutsideClickListener = () => {
    if (!outsideClickListener.value) {
        outsideClickListener.value = (event) => {
            if (isOutsideClicked(event)) {
                topbarMenuActive.value = false;
            }
        };
        document.addEventListener("click", outsideClickListener.value);
    }
};

const unbindOutsideClickListener = () => {
    if (outsideClickListener.value) {
        document.removeEventListener("click", outsideClickListener);
        outsideClickListener.value = null;
    }
};
const isOutsideClicked = (event) => {
    if (!topbarMenuActive.value) return;

    const sidebarEl = document.querySelector(".layout-topbar-menu");
    const topbarEl = document.querySelector(".layout-topbar-menu-button");

    return !(
        sidebarEl.isSameNode(event.target) ||
        sidebarEl.contains(event.target) ||
        topbarEl.isSameNode(event.target) ||
        topbarEl.contains(event.target)
    );
};
</script>

<template>
    <div class="layout-topbar">
        <router-link to="/" class="layout-topbar-logo">
            <img :src="logoUrl" alt="logo" />
            <span>SAKAI</span>
        </router-link>

        <button
            class="p-link layout-menu-button layout-topbar-button"
            @click="onMenuToggle()"
        >
            <i class="pi pi-bars"></i>
        </button>

        <button
            class="p-link layout-topbar-menu-button layout-topbar-button"
            @click="onTopBarMenuButton()"
        >
            <i class="pi pi-ellipsis-v"></i>
        </button>

        <div class="layout-topbar-menu" :class="topbarMenuClasses">
            <button
                @click="onTopBarMenuButton"
                class="p-link layout-topbar-button"
            >
                <i class="pi pi-calendar"></i>
                <span>Calendar</span>
            </button>
            <button
                @click="onProfileButton"
                class="p-link layout-topbar-button"
            >
                <i class="pi pi-user"></i>
                <span>Profile</span>
            </button>
            <Menu
                :model="items"
                ref="menuRef"
                :popup="true"
                :visible="menuVisible"
                @hide="onMenuHide"
            ></Menu>
            <button
                @click="onSettingsClick"
                class="p-link layout-topbar-button"
            >
                <i class="pi pi-cog"></i>
                <span>Settings</span>
            </button>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>
