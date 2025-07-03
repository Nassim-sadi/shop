<script setup>
import { $t } from "@/plugins/i18n";
import axios from "@/plugins/axios";
import { onMounted, ref } from "vue";

const categories = ref([]);
const loading = ref(false);

// Simplified async function
const getCategories = async () => {
    loading.value = true;
    try {
        const res = await axios.get("api/categories/leaf");
        console.log("🚀 ~ categories:", res.data);
        categories.value = res.data;
        return res.data;
    } catch (err) {
        console.error("Error fetching categories:", err);
        throw err;
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    getCategories();
});
</script>

<template>
    <div class="by-category">
        <h2>{{ $t("home.sections.by_category") }}</h2>

        <div v-if="loading" class="loading">Loading categories...</div>

        <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
                v-for="category in categories"
                :key="category.id"
                class="category-card"
            >
                <router-link
                    :to="{
                        name: 'category',
                        params: { categorySlug: category.slug },
                    }"
                    class="category-link"
                >
                    <img
                        :src="category.image"
                        :alt="category.name"
                        class="category-image"
                    />
                    <h3>{{ category.name }}</h3>
                </router-link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.category-card {
    transition: transform 0.2s ease;
}

.category-card:hover {
    transform: translateY(-2px);
}

.category-link {
    display: block;
    text-decoration: none;
    color: inherit;
}

.category-image {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 8px;
}

.loading {
    text-align: center;
    padding: 20px;
}
</style>
