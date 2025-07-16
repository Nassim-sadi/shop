<script setup>
import { $t } from "@/plugins/i18n";
import axios from "@/plugins/axios";
import { onMounted, ref } from "vue";
import router from "@/router/Index";

const categories = ref([]);
const loading = ref(false);

// Simplified async function
const getCategories = async () => {
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .get("/api/categories/homeCategories")
            .then((res) => {
                console.log("🚀 ~ .then ~ res:", res.data);
                categories.value = res.data;
                resolve(res.data);
            })
            .catch((err) => {
                console.error("Error fetching categories:", err);
                reject(err);
            })
            .finally(() => {
                loading.value = false;
            });
    });
};

onMounted(() => {
    getCategories();
});

const goToCategory = (category) => {
    router.push({
        name: "category",
        params: { categorySlug: category.slug },
    });
};
</script>

<template>
    <div class="by-category">
        <h2>{{ $t("home.sections.by_category") }}</h2>

        <div v-if="loading" class="loading">Loading categories...</div>

        <div class="category-grid" v-else>
            <div
                v-for="category in categories"
                :key="category.id"
                class="category-card"
                @click="goToCategory(category)"
            >
                <div class="category-image-container">
                    <img
                        :src="category.image"
                        :alt="category.name"
                        class="category-image"
                    />
                    <div class="category-title-overlay">
                        <h3 class="text-white">
                            {{ category.name }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped></style>
