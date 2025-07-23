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
    <div class="section-card">
        <div class="flex justify-between items-center">
            <h2>{{ $t("home.sections.by_category") }}</h2>

            <Button
                type="button"
                @click="router.push({ name: 'public-categories' })"
                outlined
            >
                {{ $t("common.show_all") }}
            </Button>
        </div>

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

<style scoped>
/* Loading Animation */
.loading {
    text-align: center;
    padding: 4rem 0;
    font-size: 1.125rem;
    color: #6b7280;
    position: relative;
}

.loading::after {
    content: "";
    position: absolute;
    left: 50%;
    bottom: -20px;
    width: 40px;
    height: 4px;
    margin-left: -20px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6, #ec4899);
    border-radius: 2px;
    animation: loading-pulse 1.5s ease-in-out infinite;
}

@keyframes loading-pulse {
    0%,
    100% {
        opacity: 0.3;
        transform: scaleX(0.8);
    }
    50% {
        opacity: 1;
        transform: scaleX(1);
    }
}
</style>
