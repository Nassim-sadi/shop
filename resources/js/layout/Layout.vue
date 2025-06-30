<script setup>
import Footer from "@/components/footer/Index.vue";
import Navbar from "@/components/header/Navbar.vue";
</script>

<template>
    <div class="layout-wrapper client">
        <div class="layout-main-container-client">
            <Navbar />
            <div class="layout-main-client">
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
    min-height: auto;
    flex-grow: 1;
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
