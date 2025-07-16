<script setup>
import Footer from "@/components/footer/Index.vue";
import Navbar from "@/components/header/Navbar.vue";
import Breadcrumbs from "@/components/Breadcrumbs.vue";
import { computed } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();

const showBreadcrumbs = computed(() => {
    const hiddenRoutes = ["/", "/cart", "/checkout", "/orders", "/account"];
    return (
        !hiddenRoutes.includes(route.path) &&
        !route.path.startsWith("/orders/") &&
        !route.path.startsWith("/account/")
    );
});
</script>

<template>
    <div class="layout-wrapper-client client">
        <div class="layout-main-container-client">
            <Navbar />
            <div class="layout-main-client">
                <Breadcrumbs v-if="showBreadcrumbs" />
                <router-view></router-view>
            </div>
        </div>
        <Footer />
        <div class="layout-mask animate-fadein"></div>
    </div>
    <Toast />
</template>

<style lang="scss" scoped>
.layout-wrapper {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.layout-main-container-client {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.layout-main-client {
    padding: 1rem;
}

@media (max-width: 960px) {
    .layout-main-client {
        padding: 1rem;
    }
}
</style>
