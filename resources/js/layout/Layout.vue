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
    /* Flexbox container for main content */
    display: flex;
    flex-direction: column;

    /* Take remaining space after navbar */
    flex: 1;
    min-height: 0; /* Allows flex children to shrink */

    /* Content spacing */
    padding: 1rem;

    /* Ensure content doesn't overflow */
    overflow-y: auto;

    /* Optional: Add some spacing from edges */
    max-width: 1600px; /* Adjust based on your design */
    margin: 0 auto;
    width: 100%;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .layout-main-client {
        padding: 0.5rem;
    }
}

@media (max-width: 480px) {
    .layout-main-client {
        padding: 0.25rem;
    }
}
</style>
