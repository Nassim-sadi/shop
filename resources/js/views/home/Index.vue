<script setup>
import ByCategory from "./sections/ByCategory.vue";
import Featured from "./sections/Featured.vue";
</script>
<template>
    <div class="home">
        <ByCategory />
        <Featured />
    </div>
</template>

<style lang="scss" scoped>
.home {
    min-height: 100vh;
}
</style>
