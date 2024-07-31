<template>
    <router-view v-slot="{ Component, route }">
        <div :key="route.name">
            <Component :is="Component" />
        </div>
    </router-view>
</template>


<script setup>
import { onMounted } from 'vue';
import axios from '@/plugins/axios';

onMounted(async () => {
    await axios.get('/sanctum/csrf-cookie').then((response) => { })
});
</script>

<style lang="scss" scoped></style>
