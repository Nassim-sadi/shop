<template>
    <div class="flex flex-col md:flex-row gap-8">
        settings page

        <Account />
        <Notifications />
    </div>
</template>

<script setup>
import Account from "./account/Index.vue";
import Notifications from "./notifications/Index.vue";
</script>

<style lang="scss" scoped></style>
